<?php

namespace Phosh\MainBundle\Test\Fixtures;

class ReflectionHelper
{
    public function createInstance($className, array $args = array(), array $properties = array(), array $calls = array())
    {
        $refClass = new \ReflectionClass($className);
        $result = $refClass->newInstanceArgs($args);
        foreach ($properties as $property => $value) {
            $this->setProperty($result, $property, $value);
        }
        foreach ($calls as $call) {
            $tmp = array_keys($call);
            $method = reset($tmp);
            $arguments = reset($call);
            $this->callMethod($result, $method, $arguments);
        }
        return $result;
    }

    public function setProperty($object, $property, $value)
    {
        $refClass = new \ReflectionClass(get_class($object));
        $property = $refClass->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    public function callMethod($object, $method, array $arguments)
    {
        $refClass = new \ReflectionClass(get_class($object));
        $method = $refClass->getMethod($method);
        $method->invokeArgs($object, $arguments);
    }

    public function getClassConstant($className, $constantName)
    {
        $refClass = new \ReflectionClass($className);
        return $refClass->getConstant($constantName);
    }
}