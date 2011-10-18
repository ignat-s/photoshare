<?php

namespace Phosh\MainBundle\Entity;
 
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
    private $attachedPhotos = array();

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->setToken($this->generateToken());
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

    public function getAttachedPhotos()
    {
        return $this->attachedPhotos;
    }

    public function hasAttachedPhoto($photo)
    {
        return in_array($photo, $this->attachedPhotos);
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

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function isTest()
    {
        return false;
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

    public function preUpdate()
    {
        if ($this->isRegenerateToken()) {
            $this->token = $this->generateToken();
        }
    }

    public function calculateAttachedPhotos()
    {
        preg_match_all('/<photo\s.*src="([^"]+)".*\/>/i', $this->body, $matches);
        if ($matches) {
            $this->attachedPhotos = array();
            foreach ($matches[1] as $photo) {
                $this->attachedPhotos[] = urldecode($photo);
            }
        } else {
            $this->attachedPhotos = array();
        }
    }

    public function getExpiredAt()
    {
        return $this->expiredAt;
    }

    public function isExpired()
    {
        return $this->expiredAt <= new \DateTime();
    }

    private function generateToken()
    {
        $bytes = false;
        if (function_exists('openssl_random_pseudo_bytes') && 0 !== stripos(PHP_OS, 'win')) {
            $bytes = openssl_random_pseudo_bytes(32, $strong);

            if (true !== $strong) {
                $bytes = false;
            }
        }

        // let's just hope we got a good seed
        if (false === $bytes) {
            $bytes = hash('sha256', uniqid(mt_rand(), true), true);
        }

        return base_convert(bin2hex($bytes), 16, 36);
    }
}
