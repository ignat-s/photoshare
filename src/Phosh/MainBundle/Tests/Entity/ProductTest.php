<?php

namespace Phosh\MainBundle\Tests\Entity;

use Phosh\MainBundle\Entity\Post;
use Phosh\MainBundle\Entity\Product;
use Phosh\MainBundle\Entity\Photo;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Phosh\MainBundle\Entity\Product
     */
    private $product;

    protected function setUp()
    {
        $this->product = new Product();
    }

    public function testIdWorks()
    {
        $this->assertNull($this->product->getId());
        $this->product->setId($id = 100);
        $this->assertEquals($id, $this->product->getId());
    }

    public function testTitleWorks()
    {
        $this->assertNull($this->product->getTitle());
        $this->product->setTitle($title = 'Title');
        $this->assertEquals($title, $this->product->getTitle());
    }

    public function testDescriptionWorks()
    {
        $this->assertNull($this->product->getDescription());
        $this->product->setDescription($description = 'Description');
        $this->assertEquals($description, $this->product->getDescription());
    }

    public function testPhotosWorks()
    {
        $this->assertEquals(0, $this->product->getPhotos()->count());

        $photo = new Photo();
        $photo->setId(100);

        $this->product->setPhotos(array($photo));

        $this->assertEquals(1, $this->product->getPhotos()->count());
        $this->assertTrue($this->product->hasPhoto($photo));

        $otherPhoto = new Photo();
        $otherPhoto->setId(200);

        $this->assertFalse($this->product->hasPhoto($otherPhoto));
    }

    public function testPostWorks()
    {
        $this->assertNull($this->product->getPost());
        $this->product->setPost($post = new Post());
        $this->assertEquals($post, $this->product->getPost());
    }

    public function testEqualsToWorks()
    {
        $this->assertFalse($this->product->equalsTo(new \stdClass()));

        $otherProduct = new Product();

        $this->assertTrue($this->product->equalsTo($otherProduct));

        $otherProduct->setTitle('Title');
        $otherProduct->setDescription('Description');
        $this->product->setTitle('Title');
        $this->product->setDescription('Description');
        $this->assertTrue($this->product->equalsTo($otherProduct));

        $this->product->setTitle('Another title');
        $this->assertFalse($this->product->equalsTo($otherProduct));

        $this->product->setId(100);
        $otherProduct->setId(100);
        $this->assertTrue($this->product->equalsTo($otherProduct));
    }
}