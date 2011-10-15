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
    
    public function getPhotoPath($photoRelativePath, $rotateAngle = 0)
    {
        if ($rotateAngle) {
            // get rotated photo
            return $this->getOrCreateConvertedPhotoPath($photoRelativePath, 0, 0, 'jpeg', $rotateAngle);
        } else {
            // get original photo path
            $photo = $this->getAbsolutePath($photoRelativePath);
            return $this->isFileExists($photo) ? $photo : null;
        }
    }

    public function getPhotoThumbPath($photoRelativePath, $width, $height, $format, $rotateAngle = 0)
    {
        return $this->getOrCreateConvertedPhotoPath($photoRelativePath, $width, $height, $format, $rotateAngle);
    }

    private function getOrCreateConvertedPhotoPath($photoRelativePath, $width, $height, $format, $rotateAngle)
    {
        if ($photo = $this->getPhotoPath($photoRelativePath)) {
            $thumb = $this->createPhotoThumbPath($photoRelativePath, $width, $height, $format, $rotateAngle);
            if (!$this->isFileExists($thumb)) {
                $imagine = new \Imagine\Gd\Imagine();
                $image = $imagine->open($photo);

                if ($width && $height) {
                    $image = $image->thumbnail(new \Imagine\Image\Box($width, $height), \Imagine\ImageInterface::THUMBNAIL_INSET);
                }
                if ($rotateAngle) {
                    $image = $image->rotate($rotateAngle);
                }
                $image->save($thumb);
            }
            return $thumb;
        }
        return null;
    }

    private function createAbsolutePath($relativePath)
    {
        return $this->photoStorageDir . '/' . $relativePath;
    }

    private function createPhotoThumbPath($photoRelativePath, $width = 0, $height = 0, $format, $rotateAngle)
    {
        $pathPart = sprintf('%s/%s', $this->thumbsDir, str_replace('/', '_', $photoRelativePath));
        $resolutionPart = $width && $height ? sprintf('_%sx%s', $width, $height) : '';
        $rotatePart = $rotateAngle ? sprintf('_r%d', $rotateAngle) : '';
        $formatPath = sprintf('.%s', $format);
        return $pathPart . $resolutionPart . $rotatePart . $formatPath;
    }

    public function isFileExists($path)
    {
        return file_exists($path) && is_file($path);
    }

    public function decodeRotateAngle($encodedRotateAngle)
    {
        return $encodedRotateAngle === '90cw' ? -90 : ($encodedRotateAngle === '90ccw' ? 90 : ($encodedRotateAngle === '180' ? 180 : 0));
    }
}