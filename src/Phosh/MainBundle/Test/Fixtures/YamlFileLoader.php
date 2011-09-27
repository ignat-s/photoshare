<?php

namespace Phosh\MainBundle\Test\Fixtures;

use Doctrine\ORM\EntityManager;

class YamlFileLoader
{
    private $manager;
    private $reflectionHelper;

    public function __construct(FixturesManager $manager)
    {
        $this->manager = $manager;
        $this->reflectionHelper = new ReflectionHelper();
    }

    public function load($file)
    {
        $data = \Symfony\Component\Yaml\Yaml::parse($file);
        if (!is_array($data)) {
            throw new \InvalidArgumentException("Attempt to load fixtures from yaml file failed. Is \"$file\" valid yaml file?");
        }
        $this->loadFromArray($data);
    }

    protected function loadFromArray(array $data)
    {
        $loaded = array();

        foreach ($data as $className => $objects) {
            foreach ($objects as $alias => $properties) {
                $key = sprintf("%s:%s", strtolower($className), $alias);
                if (isset($loaded[$key])) {
                    throw new \LogicException(sprintf("Object @%s:%s is dublicated.", $className, $alias));
                }
                $loaded[$key] = $this->reflectionHelper->createInstance($className);
            }
        }

        foreach ($data as $className => $objects) {
            foreach ($objects as $alias => $objectData) {
                $key = sprintf("%s:%s", strtolower($className), $alias);
                $object = $loaded[$key];
                
                $properties = isset($objectData['properties']) ? $objectData['properties'] : array();
                foreach ($properties as $property => $value) {
                    $this->reflectionHelper->setProperty($object, $property, $this->parseValue($value, $loaded));
                }

                $calls = isset($objectData['calls']) ? $objectData['calls'] : array();
                foreach ($calls as $call) {
                    $tmp = array_keys($call);
                    $method = reset($tmp);
                    $arguments = reset($call);
                    $arguments = is_array($arguments) ? $arguments : array($arguments);
                    $this->reflectionHelper->callMethod($object, $method, $this->parseValue($arguments, $loaded));
                }
            }
        }

        foreach ($loaded as $key => $object) {
            $alias = substr($key, strpos($key, ':') + 1);
            $this->manager->add($object, $alias);
        }
    }

    private function parseValue($value, array $loaded)
    {
        if (is_string($value) && $definition = $this->parseComplexValueDefinition($value)) {
            return $this->convertComplexValue($definition, $value, $loaded);
        } else if (is_array($value)) {
            $result = array();
            foreach ($value as $key => $v) {
                $result[$key] = $this->parseValue($v, $loaded);
            }
            return $result;
        } else {
            return $value;
        }
    }

    private function parseComplexValueDefinition($value)
    {
        $name = null;
        $arguments = array();
        if (preg_match('/^@([\w]+)(\(([^\)]+)\))?$/i', $value, $matches)) {
            $argumentsString = "";
            if (isset($matches[1])) {
                $name = $matches[1];
            }
            if (isset($matches[3])) {
                $argumentsString = $matches[3];
            }
            if (preg_match_all('/"([^",]*)"/i', $argumentsString, $matches)) {
                foreach ($matches[1] as $argument) {
                    if ($argument === "") {
                        $argument = null;
                    }
                    $arguments[] = $argument === "" ? null : $argument;
                }
            }
            return array(strtolower($name), $arguments);
        }
        return null;
    }

    private function convertComplexValue($definition, $value, array $loaded)
    {
        list($name, $arguments) = $definition;
        
        switch ($name) {
            case 'fixture':
                if (count($arguments) !== 2) {
                    throw new \LogicException(sprintf('Expected 2 parameters in definition of @fixture, %d given.', count($arguments)));
                }
                list($className, $alias) = $arguments;
                $refKey = sprintf("%s:%s", strtolower($className), $alias);
                if ($this->manager->has($className, $alias)) {
                    return $this->manager->get($className, $alias);
                } else if (isset($loaded[$refKey])) {
                    return $loaded[$refKey];
                } else {
                    throw new \LogicException(sprintf("Unable to load reference to object @%s:%s.", $className, $alias));
                }
                break;
            case 'constant':
                if (count($arguments) == 2) {
                    list($className, $constantName) = $arguments;
                    return $this->reflectionHelper->getClassConstant($className, $constantName);
                } else if (count($arguments) == 1) {
                    list($constantName) = $arguments;
                    return constant($constantName);
                } else {
                    throw new \LogicException(sprintf('Expected 2 or 1 parameters in definition of @constant, %d given.', count($arguments)));
                }
            case 'datetime':
                $result = new \DateTime(isset($arguments[0]) ? $arguments[0] : null);
                if (isset($arguments[1])) {
                    $result->modify($arguments[1]);
                }
                return $result;
                break;
            default:
                throw new \LogicException(sprintf("Unable to convert @%s(%s) to value.", $name, implode(', ', $arguments)));
        }
    }
}
