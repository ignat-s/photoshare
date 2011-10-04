<?php

namespace Phosh\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Phosh\MainBundle\Entity\Post;
use Phosh\MainBundle\Form\Type\PostType;

/**
 * @Route("/filestorage")
 * @Secure(roles="ROLE_ADMIN")
 */
class FileStorageController extends BaseController
{
    /**
     * @Route("/tree", name="filestorage_tree", requirements={"_method"="POST"})
     * @Template()
     */
    public function treeAction()
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
     * @Route("/image_thumb.{format}", name="filestorage_imagethumb", requirements={"format"="jpg|jpeg|png"}, defaults={"format"="jpeg"})
     * @Template()
     */
    public function imageThumbAction($format)
    {
        $width = $this->getRequest()->get('h', 100);
        $height = $this->getRequest()->get('w', 100);
        $path = $this->getRequest()->get('p');
        
        $photoThumb = $this->getPhotoStorage()->getPhotoThumbPath($path, $width, $height, $format);
        return $this->createImageResponse($photoThumb, $format);
    }

    private function createImageResponse($imagePath, $format)
    {
        return new \Symfony\Component\HttpFoundation\Response(file_get_contents($imagePath), 200, array(
                'Content-Type' => 'image/' . $format,
            ));
    }

    /**
     * @return \Phosh\MainBundle\PhotoStorage
     */
    public function getPhotoStorage()
    {
        return $this->get('phosh.photo_storage');
    }
}