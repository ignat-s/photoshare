<?php

namespace Phosh\MainBundle\Test;

use Phosh\MainBundle\Test\Fixtures\FixturesManager;

class FixturableTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    /**
     * @var \Phosh\MainBundle\Test\FixturesManager
     */
    static private $fixtures;
    static private $em;

	static public function setUpBeforeClass()
	{
		$kernel = self::createKernel();
		$kernel->boot();

        $container = $kernel->getContainer();

		self::$em = $container->get('doctrine')->getEntityManager();
        self::$fixtures = new FixturesManager(self::$em);

		static::loadFixtures(self::$fixtures);
		self::$fixtures->flush();
	}

    static protected function loadFixtures()
	{

	}

    /**
     * @return \Phosh\MainBundle\Test\FixturesManager
     */
    protected function getFixturesManager()
    {
        return self::$fixtures;
    }

    protected static function loadFixturesFromYamlFile($file)
    {
        self::$fixtures->loadFromYamlFile($file);
    }

    static protected function reloadFixtures()
    {
        self::$fixtures->clear();
        static::loadFixtures();
        self::$fixtures->flush();
    }

	static public function tearDownAfterClass()
	{
        self::$fixtures->clear();
	}

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
	 * @return \Doctrine\ORM\EntityManager
	 */
	protected function getEntityManager()
	{
        return self::$em;
	}

    /**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	protected function getRepository($objectName)
	{
		return $this->getEntityManager()->getRepository($objectName);
	}
}
