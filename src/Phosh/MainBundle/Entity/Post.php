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

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->setRandomToken();
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

    public function getOwner()
    {
        return $this->owner;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function setRandomToken()
    {
        $this->setToken($this->generateToken());
    }

    public function getToken()
    {
        return $this->token;
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
