<?php

namespace Phosh\MainBundle\Test\Fixtures;

use Doctrine\ORM\EntityManager;

class FixturesManager
{
	private $em;
	private $objects = array();
    private $loader;
    private $reflectionHelper;

	public function __construct(EntityManager $entityManager)
	{
		$this->em = $entityManager;
        $this->loader = new YamlFileLoader($this);
        $this->reflectionHelper = new ReflectionHelper();
	}

    public function flush()
	{
		$this->em->flush();
	}

	public function clear()
	{
        $objects = array();
		foreach ($this->objects as $objects) {
			foreach ($objects as $object) {
				$objects[] = $object;
			}
		}
		$objects = array_reverse($objects);

        foreach ($objects as $object) {
            $this->em->remove($object);
        }

        foreach ($this->em->getUnitOfWork()->getIdentityMap() as $map) {
			foreach ($map as $entity) {
				$this->em->remove($entity);
			}
		}
        $this->em->flush();
        $this->em->clear();
        $this->objects = array();
	}

	public function create($className, array $data = array(), $key = null)
	{
        if (null === $key) {
            $key = reset($data);
        }
        $object = $this->reflectionHelper->createInstance($className, array(), $data);
        $this->add($object, $key);
		return $object;
	}

    public function createList($className, array $dataList = array(), $keyIndex = null)
	{
        $result = array();
        foreach ($dataList as $data) {
            if (null === $keyIndex) {
                $key = null;
            } else if (is_string($keyIndex)) {
                if (isset($data[$keyIndex])) {
                    $key = $data[$keyIndex];
                } else {
                    throw new \InvalidArgumentException("Element of \$dataList has no value with \$keyIndex '$keyIndex'.");
                }
            } else {
                throw new \InvalidArgumentException('$keyIndex has invalid value.');
            }
            $result[] = $this->create($className, $data, $key);
        }
		return $result;
	}

	public function add($object, $key = null)
	{
        if (!is_object($object)) {
            throw new \InvalidArgumentException('$object is not object.');
        }
        if (null === $key) {
            if (method_exists($object, '__toString')) {
                $key = (string) $object;
            } else if (method_exists($object, 'getId') && $object->getId()) {
                $key = $object->getId();
            } else {
                throw new \InvalidArgumentException('$key is empty and can\'t be guessed.');
            }
        }
        $key = $this->validateKey($key);
        $class = get_class($object);
		$this->objects[$class][$key] = $object;
        $this->em->persist($object);
	}

	public function getList($class, $keys = array())
	{
		if (is_string($keys)) {
			if (!$keys) {
				return array();
			}
			$keys = preg_split('/\s*,\s*/', $keys);
		} else if (!is_array($keys)) {
			throw new \InvalidArgumentException('$keys valid type should be array or string.');
		}
		$result = array();
        if (!$keys) {
            if (!isset($this->objects[$class])) {
                throw new \InvalidArgumentException("Fixture objects with \$class equals $class not exist.");
            }
            return array_values($this->objects[$class]);
        }
		foreach ($keys as $key) {
			$result[] = $this->get($class, $key);
		}
		return $result;
	}

	public function get($class, $key)
	{
        $key = $this->validateKey($key);
		if ($this->has($class, $key)) {
			return $this->objects[$class][$key];
		} else {
			throw new \LogicException(sprintf("Fixture object %s with key \"%s\" not exist.", $class, $key));
		}
	}

	public function has($class, $key)
	{
		return isset($this->objects[$class][$this->validateKey($key)]);
	}

    private function validateKey($key)
    {
        $key = trim($key);
        if (!$key) {
            throw new \InvalidArgumentException('$key should not be empty.');
        }
        return $key;
    }

    public function loadFromYamlFile($file)
    {
        $this->loader->load($file);
    }
}