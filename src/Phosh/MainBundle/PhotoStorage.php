<?php

namespace Phosh\MainBundle;

use Phosh\MainBundle\Exception\RuntimeException;

class PhotoStorage
{
    private $photoStorageDir;
    private $thumbsDir;

    public function __construct($photoStorageDir, $thumbsDir)
    {
        if (!is_dir($photoStorageDir) && !mkdir($photoStorageDir, 0755, true)) {;
            throw new RuntimeException(sprintf('Can\'t create storage directory "%s".', $photoStorageDir));
        }
        $this->photoStorageDir = $photoStorageDir;

        if (!is_dir($thumbsDir) && !mkdir($thumbsDir, 0755, true)) {
            throw new RuntimeException(sprintf('Can\'t create storage photos thumbs directory "%s".', $thumbsDir));
        }
        $this->thumbsDir = $thumbsDir;
    }

    public function getAbsolutePath($relativePath)
    {
        return $this->createAbsolutePath(preg_replace('/^[\/]*/', '', $relativePath));
    }
    
    public function getPhotoPath($photoRelativePath)
    {
        $photo = $this->getAbsolutePath($photoRelativePath);

        return $this->isFileExists($photo) ? $photo : null;
    }

    public function getPhotoThumbPath($photoRelativePath, $width, $height, $format)
    {
        if ($photo = $this->getPhotoPath($photoRelativePath)) {
            $thumb = $this->createPhotoThumbPath($photoRelativePath, $width, $height, $format);
            if (!$this->isFileExists($thumb)) {
                $imagine = new \Imagine\Gd\Imagine();
                $imagine->open($photo)
                        ->thumbnail(new \Imagine\Image\Box($width, $height), \Imagine\ImageInterface::THUMBNAIL_INSET)
                        ->save($thumb);
            }
            return $thumb;
        }
        return null;
    }

    private function createAbsolutePath($relativePath)
    {
        return $this->photoStorageDir . '/' . $relativePath;
    }

    private function createPhotoThumbPath($photoRelativePath, $width, $height, $format)
    {
        return sprintf('%s/%s_%sx%s.%s', $this->thumbsDir, str_replace('/', '_', $photoRelativePath), $width, $height, $format);
    }

    public function isFileExists($path)
    {
        return file_exists($path) && is_file($path);
    }
}