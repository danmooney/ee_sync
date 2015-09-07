<?php

class Syncee_Helper_Reflection
{
    public static function getAllPropertyNamesOfObject($obj)
    {
        $property_names        = array();
        $reflection_class      = new ReflectionClass($obj);
        $reflection_properties = $reflection_class->getProperties();

        foreach ($reflection_properties as $reflection_property) {
            if ($reflection_property->isStatic()) {
                continue;
            }

            $property_names[] = $reflection_property->getName();
        }

        return $property_names;
    }
}