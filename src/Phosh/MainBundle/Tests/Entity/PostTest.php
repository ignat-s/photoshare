<?php

namespace Phosh\MainBundle\Tests\Entity;

use Phosh\MainBundle\Entity\Post;

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
        $this->post->setOwner($owner = 'someone');
        $this->assertEquals($owner, $this->post->getOwner());
    }

    public function testTokenWorks()
    {
        $this->assertNotNull($this->post->getToken());
        $this->post->setToken($token = 'SomeToken');
        $this->assertEquals($token, $this->post->getToken());
    }

    public function testExpiredAtWorks()
    {
        $this->assertNull($this->post->getExpiredAt());
        $this->post->setExpiredAt($expiredAt = new \DateTime());
        $this->assertEquals($expiredAt, $this->post->getExpiredAt());
    }
}