<?php

namespace FDT\doctrineExtensions\Timestampable;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventArgs;
use Doctrine\ODM\MongoDB\Events;

use FDT\doctrineExtensions\Exception\InvalidMappingException;


class TimestampableListener implements EventSubscriber
{
    private $_validTypes = array(
        'date',
        'time',
        'datetime',
        'timestamp'
    );
    
    
    
    protected $configurations = array();
    
    
    /**
     * Specifies the list of events to listen
     * 
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::onFlush,
            Events::loadClassMetadata
        );
    }
    
	public function loadClassMetadata(\Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();                
        $reflClass = $classMetadata->getReflectionClass();
                
        $reader = $this->getReader ();
        
        $config = array ();
        
        
                                        
        foreach ($reflClass->getProperties() as $property)
        {
                
        	$timestampable = $reader->getPropertyAnnotation($property, '\FDT\doctrineExtensions\Timestampable\Mapping\Timestampable');
        	
        	if ($timestampable)
        	{

        		$field = $property->getName();
                if (!$classMetadata->hasField($field))
                {
                    throw new InvalidMappingException("Unable to find timestampable [{$field}] as mapped property in entity - {$classMetadata->name}");
                }
                if (!$this->_isValidField($classMetadata, $field))
                {
                    throw new InvalidMappingException("Field - [{$field}] type is not valid and must be 'date', 'datetime' or 'time' in class - {$classMetadata->name}");
                }
                if (!in_array($timestampable->on, array('update', 'create', 'change')))
                {
                    throw new InvalidMappingException("Field - [{$field}] trigger 'on' is not one of [update, create, change] in class - {$classMetadata->name}");
                }
                if ($timestampable->on == 'change')
                {
                    if (!isset($timestampable->field) || !isset($timestampable->value))
                    {
                        throw new InvalidMappingException("Missing parameters on property - {$field}, field and value must be set on [change] trigger in class - {$meta->name}");
                    }
                    $field = array(
                        'field' => $field,
                        'trackedField' => $timestampable->field,
                        'value' => $timestampable->value 
                    );
                }
                // properties are unique and mapper checks that, no risk here
                $config[$timestampable->on][] = $field;
        	        	
        	}
        	
        }
        
        if (count ($config) > 0)
        {
        
        	$this->configurations[$classMetadata->name] = $config;
        
        }
        
    }
    
    
    public function getConfiguration($objectManager, $class)
    {
        $config = array();
        if (isset($this->configurations[$class]))
        {
            $config = $this->configurations[$class];
        }
        else
        {
            $cacheDriver = $objectManager->getMetadataFactory()->getCacheDriver();
            $cacheId = $this->getCacheId($class, $this->getNamespace());
            if ($cacheDriver && ($cached = $cacheDriver->fetch($cacheId)) !== false)
            {
                $this->configurations[$class] = $cached;
                $config = $cached;
            }
        }
        return $config;
    }
    
    /**
     * Get the cache id
     *
     * @param string $className
     * @param string $extensionNamespace
     * @return string
     */
    public function getCacheId($className, $extensionNamespace)
    {
        return $className . '\\$' . strtoupper(str_replace('\\', '_', $extensionNamespace)) . '_CLASSMETADATA';
    }
    
    public function prePersist(EventArgs $args)
    {
        $om = $args->getDocumentManager();
        $object = $args->getDocument();
        
        $meta = $om->getClassMetadata(get_class($object));
        
        if ($config = $this->getConfiguration($om, $meta->name))
        {
            if (isset($config['update']))
            {
                foreach ($config['update'] as $field)
                {
                    if ($meta->getReflectionProperty($field)->getValue($object) === null)
                    {
                        $meta->getReflectionProperty($field)->setValue($object, $this->getDateValue($meta, $field));
                        
                    }
                }
            }
            
            if (isset($config['create']))
            {
                foreach ($config['create'] as $field)
                {
                    if ($meta->getReflectionProperty($field)->getValue($object) === null)
                    {
                        $meta->getReflectionProperty($field)->setValue($object, $this->getDateValue($meta, $field));
                    }
                }
            }
        }
    }
    
    
    public function onFlush(EventArgs $args)
    {
        $om = $args->getDocumentManager();
        $uow = $om->getUnitOfWork();
        // check all scheduled updates
        foreach ($uow->getScheduledDocumentUpdates() as $object)
        {
            $meta = $om->getClassMetadata(get_class($object));
            if ($config = $this->getConfiguration($om, $meta->name))
            {
                $changeSet = $uow->getDocumentChangeSet($object);
                $needChanges = false;

                if (isset($config['update']))
                {
                    foreach ($config['update'] as $field)
                    {
                        if (!isset($changeSet[$field]))
                        {
                            $needChanges = true;
                            $meta->getReflectionProperty($field)->setValue($object, $this->getDateValue($meta, $field));
                        }
                    }
                }
                
                if (isset($config['change']))
                {
                    foreach ($config['change'] as $options)
                    {
                        if (isset($changeSet[$options['field']]))
                        {
                            continue; // value was set manually
                        }
                        
                        $tracked = $options['trackedField'];
                        $trackedChild = null;
                        $parts = explode('.', $tracked);
                        if (isset($parts[1]))
                        {
                            $tracked = $parts[0];
                            $trackedChild = $parts[1];
                        }
                        
                        if (isset($changeSet[$tracked]))
                        {
                            $changes = $changeSet[$tracked];
                            if (isset($trackedChild))
                            {
                                $changingObject = $changes[1];
                                if (!is_object($changingObject))
                                {
                                    throw new \dinoWeb\Exception\UnexpectedValueException("Field - [{$field}] is expected to be object in class - {$meta->name}");
                                }
                                $objectMeta = $om->getClassMetadata(get_class($changingObject));
                                $trackedChild instanceof Proxy && $om->refresh($trackedChild);
                                $value = $objectMeta->getReflectionProperty($trackedChild)
                                    ->getValue($changingObject);
                            }
                            else
                            {
                                $value = $changes[1];
                            }
                            
                            if ($options['value'] == $value)
                            {
                                $needChanges = true;
                                $meta->getReflectionProperty($options['field'])
                                     ->setValue($object, $this->getDateValue($meta, $options['field']));
                            }
                        }
                    }
                }
                
                if ($needChanges)
                {
                    $uow->recomputeSingleDocumentChangeSet($meta, $object);
                }
            }
        }
    }
    
    private function getReader ()
    {
    
    	require_once __DIR__ . '/Mapping/Timestampable.php';
        $reader = new AnnotationReader();
        $reader->setAnnotationNamespaceAlias('FDT\doctrineExtensions\Timestampable\Mapping\\', 'FDT');
        
        return $reader;
    
    
    }
    
    protected function _isValidField($meta, $field)
    {
        $mapping = $meta->getFieldMapping($field);
        return $mapping && in_array($mapping['type'], $this->_validTypes);
    }
    
    protected function getDateValue($meta, $field)
    {
        $mapping = $meta->getFieldMapping($field);
        if (isset($mapping['type']) && $mapping['type'] === 'timestamp') {
            return time();
        }
        return new \DateTime();
    }



}