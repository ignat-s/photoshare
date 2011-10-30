<?php

namespace Phosh\MainBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Order
{
    const CLASS_NAME = 'Phosh\MainBundle\Entity\Order';

    private $id;
    private $customer;
    private $contact;
    private $comment;
    private $post;
    private $products;
    private $createdAt;

    public function __construct(Post $post)
    {
        $this->createdAt = new \DateTime();
        $this->products = new ArrayCollection();
        $this->post = $post;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNumber()
    {
        return $this->id;
    }

    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    public function getCustomer()
    {
        return $this->customer;
    }

    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    public function getContact()
    {
        return $this->contact;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return \Phosh\MainBundle\Entity\Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    public function setProducts($products)
    {
        $this->products->clear();
        foreach ($products as $product) {
            if (!$this->hasProduct($product)) {
                $this->products->add($product);
            }
        }
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

    public function isProductsNotEmpty()
    {
        return count($this->products) > 0;
    }

    public function removeProduct(Product $product)
    {
        $this->products->removeElement($product);
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \Phosh\MainBundle\Entity\Post
     */
    public function getPost()
    {
        return $this->post;
    }
}
