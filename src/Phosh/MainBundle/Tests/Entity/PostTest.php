<?php

namespace Phosh\MainBundle\Tests\Entity;

use Phosh\MainBundle\Entity\Product;
use Phosh\MainBundle\Entity\Post;
use Phosh\MainBundle\Entity\User;

class PostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Phosh\MainBundle\Entity\Post
     */
    private $post;

    protected function setUp()
    {
        $this->post = new Post();
    }

    public function testIdWorks()
    {
        $this->assertNull($this->post->getId());
        $this->post->setId($id = 100);
        $this->assertEquals($id, $this->post->getId());
    }

    public function testTitleWorks()
    {
        $this->assertNull($this->post->getTitle());
        $this->post->setTitle($title = 'Text');
        $this->assertEquals($title, $this->post->getTitle());
    }

    public function testBodyWorks()
    {
        $this->assertNull($this->post->getBody());
        $this->post->setBody($body = 'Text');
        $this->assertEquals($body, $this->post->getBody());
    }

    public function testProductsWorks()
    {
        $this->assertEquals(0, $this->post->getProducts()->count());

        $product = new Product();
        $product->setId(100);

        $this->post->setProducts(array($product));

        $this->assertEquals(1, $this->post->getProducts()->count());
        $this->assertTrue($this->post->hasProduct($product));

        $otherProduct = new Product();
        $otherProduct->setId(200);

        $this->assertFalse($this->post->hasProduct($otherProduct));
    }

    public function testCreatedAtWorks()
    {
        $this->assertInstanceOf('DateTime', $this->post->getCreatedAt());
        $this->post->setCreatedAt($createdAt = new \DateTime());
        $this->assertEquals($createdAt, $this->post->getCreatedAt());
    }

    public function testUpdatedAtWorks()
    {
        $this->assertNull($this->post->getUpdatedAt());
        $this->post->setUpdatedAt($updatedAt = new \DateTime());
        $this->assertEquals($updatedAt, $this->post->getUpdatedAt());
    }

    public function testOwnerWorks()
    {
        $this->assertNull($this->post->getOwner());
        $this->post->setOwner($owner = new User());
        $this->assertEquals($owner, $this->post->getOwner());
    }

    public function testTokenWorks()
    {
        $this->assertNull($this->post->getToken());
        $this->post->regenerateToken();
        $this->assertNotNull($this->post->getToken());
        $this->post->setToken($token = 'SomeToken');
        $this->assertEquals($token, $this->post->getToken());
    }

    public function testRegenerateTokenWorks()
    {
        $this->assertFalse($this->post->isRegenerateToken());
        $this->post->setRegenerateToken(true);
        $this->assertTrue($this->post->isRegenerateToken());
    }

    public function testExpiredAtWorks()
    {
        $this->assertNull($this->post->getExpiredAt());
        $this->post->setExpiredAt($expiredAt = new \DateTime());
        $this->assertEquals($expiredAt, $this->post->getExpiredAt());
        $this->assertTrue($this->post->isExpired());
    }
}