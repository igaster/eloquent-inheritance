<?php namespace igaster\EloquentInheritance;

trait EloquentInherited {

    public function setAttributesArray($data){
        $this->attributes=$data;
    }

}