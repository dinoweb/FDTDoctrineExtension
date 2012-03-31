<?php

namespace FDT\doctrineExtensions\Timestampable\Mapping;

use Doctrine\Common\Annotations\Annotation;

/**
 * TreeLeft annotation for Tree behavioral extension
 *
 * @Annotation
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Mapping.Annotation
 * @subpackage TreeLeft
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
final class Timestampable extends Annotation
{

    public $on = 'update';
    public $field;
    public $value;

}