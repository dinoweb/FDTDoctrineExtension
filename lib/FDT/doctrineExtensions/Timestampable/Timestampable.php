<?php

namespace FDT\doctrineExtensions\Timestampable;


interface Timestampable
{
    // timestampable expects annotations on properties
    
    /**
     * @dinoWeb:Timestampable(on="create")
     * dates which should be updated on insert only
     */
    
    /**
     * @gedmo:Timestampable(on="update")
     * dates which should be updated on update and insert
     */
    
    /**
     * @gedmo:Timestampable(on="change", field="field", value="value")
     * dates which should be updated on changed "property" 
     * value and become equal to given "value"
     */
    
    /**
     * example
     * 
     * @gedmo:Timestampable(on="create")
     * @Column(type="date")
     * $created
     */
}