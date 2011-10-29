<?php

namespace Phosh\MainBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Post
{
    const CLASS_NAME = 'Phosh\MainBundle\Entity\Post';

    private $id;
    private $title;
    private $body;
    private $token;
    private $owner;
    private $createdAt;
    private $expiredAt;
    private $updatedAt;
    private $regenerateToken = false;
    private $products;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->products = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function addProduct(Product $product)
    {
        $this->products->add($product);
    }

    public function hasProduct(Product $product)
    {
        foreach ($this->products as $actualProduct) {
            if ($product->equalsTo($actualProduct)) {
                return true;
            }
        }

        return false;
    }

    public function removeProduct(Product $product)
    {
        $this->products->removeElement($product);
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setOwner(User $user)
    {
        $this->owner = $user;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function regenerateToken()
    {
        $this->setToken(mt_rand(1000000000, 9999999999));
    }

    public function setRegenerateToken($regenerateToken)
    {
        $this->regenerateToken = $regenerateToken;
    }

    public function isRegenerateToken()
    {
        return $this->regenerateToken;
    }

    public function setExpiredAt(\DateTime $expiredAt)
    {
        $this->expiredAt = $expiredAt;
    }

    public function getExpiredAt()
    {
        return $this->expiredAt;
    }

    public function isExpired()
    {
        return $this->expiredAt <= new \DateTime();
    }
}
