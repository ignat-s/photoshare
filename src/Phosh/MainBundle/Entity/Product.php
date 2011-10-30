<?php

namespace Phosh\MainBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Product
{
    const CLASS_NAME = 'Phosh\MainBundle\Entity\Product';

    private $id;
    private $title;
    private $description;
    private $photos;
    private $owner;
    private $createdAt;
    private $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->photos = new ArrayCollection();
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

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getPhotos()
    {
        return $this->photos;
    }

    public function setPhotos($photos)
    {
        $this->clearPhotos();
        foreach ($photos as $photo) {
            $this->addPhoto($photo);
        }
    }

    public function addPhoto(Photo $photo)
    {
        $photo->setProduct($this);
        $this->photos->add($photo);
    }

    public function hasPhoto(Photo $photo)
    {
        foreach ($this->photos as $actualPhoto) {
            if ($photo->equalsTo($actualPhoto)) {
                return true;
            }
        }

        return false;
    }

    public function clearPhotos()
    {
        $this->photos->clear();
    }

    public function setOwner(User $user)
    {
        $this->owner = $user;
    }

    public function getOwner()
    {
        return $this->owner;
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
