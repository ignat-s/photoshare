<?php

namespace Phosh\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Phosh\MainBundle\Entity\Photo;

/**
 * @Route("/photostorage")
 * @Secure(roles="ROLE_ADMIN")
 */
class PhotostorageController extends BaseController
{
    /**
     * @Route("/filetree", name="photostorage_filetree")
     * @Template()
     */
    public function filetreeAction()
    {
        $relativeDir = $this->getRequest()->get('dir');
        $absolutedir = $this->getPhotoStorage()->getAbsolutePath($relativeDir);

        $this->assertTrue(is_dir($absolutedir));

        $items = scandir($absolutedir);
        natcasesort($items);

        $elements = array();

        foreach ($items as $item) {
            if ($item != '.' && $item != '..') {
                $element = array(
                    'name' => $item,
                    'path' => str_replace('//', '/', $relativeDir . '/' . $item),
                    'type' =>  is_dir($absolutedir . '/' . $item) ? 'dir' : 'file',
                );
                if ('file' == $element['type']) {
                    $fileNameArray = explode('.', $element['name']);
                    $element['ext'] = end($fileNameArray);
                }
                $elements[] = $element;
            }
        }

        return array(
            'elements' => $elements,
        );
    }

    /**
     * @Route("/photo.jpeg", name="photostorage_photo", requirements={"format"="jpg|jpeg|png"}, defaults={"format"="jpeg"})
     * @Template()
     */
    public function photoAction($format)
    {
        $width = $this->getRequest()->get('h', 0);
        $height = $this->getRequest()->get('w', 0);
        
        if ($id = $this->getRequest()->get('id')) {
            $photo = $this->findPhoto($id);
            $this->assertNotNull($photo);
            $path = $photo->getPath();
            $rotateAngle = $photo->getRotateAngle();
        } else if ($this->getRequest()->get('p')) {
            $path = $this->getRequest()->get('p');
            $rotateAngle = $this->getPhotoStorage()->decodeRotateAngle($this->getRequest()->get('r'));
        } else {
            $this->fail(null, 400);
        }

        if ($width || $height) {
            $photoPath = $this->getPhotoStorage()->getPhotoThumbPath($path, $width, $height, $format, $rotateAngle);
        } else {
            $photoPath = $this->getPhotoStorage()->getPhotoPath($path, $format, $rotateAngle);
        }

        return $this->getImageResponseFactory()->createImageResponse($photoPath, $format);
    }

    /**
     * @return \Phosh\MainBundle\HttpFoundation\ImageResponseFactory
     */
    private function getImageResponseFactory()
    {
        return $this->get('phosh.http.image_response_factory');
    }

    /**
     * @return \Phosh\MainBundle\PhotoStorage
     */
    public function getPhotoStorage()
    {
        return $this->get('phosh.photo_storage');
    }

    /**
     * @param $id
     * @return \Phosh\MainBundle\Entity\Photo
     */
    private function findPhoto($id)
    {
        return $this->getRepository(Photo::CLASS_NAME)->find($id);
    }
}