<?php

namespace Phosh\MainBundle\Entity;

class Photo implements \Serializable
{
    const CLASS_NAME = 'Phosh\MainBundle\Entity\Photo';

    private $id;
    private $path;
    private $rotateAngle;
    private $product;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function pathEquals($path)
    {
        return $this->path == $path;
    }

    public function setRotateAngle($rotateAngle)
    {
        $this->rotateAngle = $rotateAngle;
    }

    public function getRotateAngle()
    {
        return $this->rotateAngle;
    }

    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function getHash()
    {
        return md5($this->getPath() . $this->getRotateAngle());
    }

    public function equalsTo($other)
    {
        if (!$other instanceof self) {
            return false;
        }
        return $this->path == $other->path && $this->rotateAngle == $other->rotateAngle;
    }

    public function serialize()
    {
        return serialize(array(
            $this->path,
            $this->rotateAngle,
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->path,
            $this->rotateAngle,
        ) = unserialize($serialized);
    }
}
