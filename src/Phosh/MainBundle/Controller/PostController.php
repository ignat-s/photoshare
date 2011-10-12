<?php

namespace Phosh\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Phosh\MainBundle\Entity\Post;
use Phosh\MainBundle\Form\Type\PostType;

/**
 * @Route("/posts")
 */
class PostController extends BaseController
{
    /**
     * @Route("/{token}.html", name="post_show_by_token", requirements={"_method"="GET", "token" = "[\w\d]+"})
     * @ParamConverter("token", class="PhoshMainBundle:Post")
     * @Template()
     */
    public function showAction(Post $post)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->assertFalse($post->isExpired());
        }
        return array(
            'post' => $post,
        );
    }

    /**
     * @Route("/{token}/images/thumb", name="post_image_thumb")
     * @ParamConverter("token", class="PhoshMainBundle:Post")
     * @Template()
     */
    public function thumbImageAction(Post $post, $format = 'jpeg')
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->assertFalse($post->isExpired());
        }

        $width = $this->getRequest()->get('h', 640);
        $height = $this->getRequest()->get('w', 640);
        $path = $this->getRequest()->get('p');

        $photoThumb = $this->getPhotoStorage()->getPhotoThumbPath($path, $width, $height, $format);
        return $this->createImageResponse($photoThumb, $format);
    }

    /**
     * @Route("/{token}/images/full", name="post_image_full")
     * @ParamConverter("token", class="PhoshMainBundle:Post")
     * @Template()
     */
    public function fullImageAction(Post $post, $format = 'jpeg')
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->assertFalse($post->isExpired());
        }

        $path = $this->getRequest()->get('p');

        $photoThumb = $this->getPhotoStorage()->getPhotoPath($path, $format);
        return $this->createImageResponse($photoThumb, $format);
    }

    private function createImageResponse($imagePath, $format)
    {
        $fileModTime = filemtime($imagePath);

        $headers = array(
            'Content-Type' => 'image/' . $format,
        );

        // Checking if the client is validating his cache and if it is current.
        if (($this->getRequest()->headers->has('If-Modified-Since')) && (strtotime($this->getRequest()->headers->get('If-Modified-Since')) == $fileModTime)) {
            // Client's cache IS current, so we just respond '304 Not Modified'.
            $headers['Last-Modified'] = gmdate('D, d M Y H:i:s', $fileModTime) . ' GMT';
            $responseCode = 304;
        } else {
            // Image not cached or cache outdated, we respond '200 OK' and output the image.
            $headers += array(
                'Last-Modified' => gmdate('D, d M Y H:i:s', $fileModTime) . ' GMT',
                'Content-transfer-encoding' => 'binary',
                'Content-length' => filesize($imagePath),
            );
            $responseCode = 200;
        }

        return new \Symfony\Component\HttpFoundation\Response(file_get_contents($imagePath), $responseCode, $headers);
    }

    /**
     * @return \Phosh\MainBundle\PhotoStorage
     */
    public function getPhotoStorage()
    {
        return $this->get('phosh.photo_storage');
    }
}