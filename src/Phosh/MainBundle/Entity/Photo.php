<?php

namespace Phosh\MainBundle\Entity;

class Photo
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

    public function equalsTo($other)
    {
        if (!$other instanceof self) {
            return false;
        }
        if ($this->id || $other->id) {
            return $this->id == $other->id;
        }
        return $this == $other;
    }
}
