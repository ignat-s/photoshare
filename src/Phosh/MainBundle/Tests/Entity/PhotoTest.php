<?php

namespace Phosh\MainBundle\Tests\Entity;

use Phosh\MainBundle\Entity\Product;
use Phosh\MainBundle\Entity\Photo;

class PhotoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Phosh\MainBundle\Entity\Photo
     */
    private $photo;

    protected function setUp()
    {
        $this->photo = new Photo();
    }

    public function testIdWorks()
    {
        $this->assertNull($this->photo->getId());
        $this->photo->setId($id = 100);
        $this->assertEquals($id, $this->photo->getId());
    }

    public function testPathWorks()
    {
        $this->assertNull($this->photo->getPath());
        $this->photo->setPath($path = '/some/path/photo.jpg');
        $this->assertEquals($path, $this->photo->getPath());
        $this->assertTrue($this->photo->pathEquals($path));
        $this->assertFalse($this->photo->pathEquals('other/path/photo.jpg'));
    }

    public function testRotateAngleWorks()
    {
        $this->assertNull($this->photo->getRotateAngle());
        $this->photo->setRotateAngle($ra = 180);
        $this->assertEquals($ra, $this->photo->getRotateAngle());
    }

    public function testProductWorks()
    {
        $this->assertNull($this->photo->getProduct());
        $this->photo->setProduct($product = new Product());
        $this->assertEquals($product, $this->photo->getProduct());
    }

    public function testEqualsToWorks()
    {
        $this->assertFalse($this->photo->equalsTo(new \stdClass()));

        $otherPhoto = new Photo();

        $this->assertTrue($this->photo->equalsTo($otherPhoto));

        $otherPhoto->setPath('some/path');
        $otherPhoto->setRotateAngle(90);
        $this->photo->setPath('some/path');
        $this->photo->setRotateAngle(90);
        $this->assertTrue($this->photo->equalsTo($otherPhoto));

        $this->photo->setRotateAngle(null);
        $this->assertFalse($this->photo->equalsTo($otherPhoto));

        $this->photo->setId(100);
        $otherPhoto->setId(100);
        $this->assertTrue($this->photo->equalsTo($otherPhoto));
    }
}