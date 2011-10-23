<?php

namespace Phosh\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Phosh\MainBundle\Entity\Post;
use Phosh\MainBundle\Form\Type\PostType;

/**
 * @Route("/{token}", requirements={"token"="\d+"})
 */
class PostController extends BaseController
{
    /**
     * @Route("", name="post_show_by_token", requirements={"_method"="GET"})
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
     * @Route("/i/t", name="post_image_thumb")
     * @ParamConverter("token", class="PhoshMainBundle:Post")
     * @Template()
     */
    public function thumbImageAction(Post $post, $format = 'jpeg')
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->assertFalse($post->isExpired());
        }

        $path = $this->getRequest()->get('p');
        $this->assertTrue($post->hasAttachedPhoto($path));

        $width = $this->getRequest()->get('h', 640);
        $height = $this->getRequest()->get('w', 640);

        $rotateAngle = $this->getPhotoStorage()->decodeRotateAngle($this->getRequest()->get('r'));

        $photoThumb = $this->getPhotoStorage()->getPhotoThumbPath($path, $width, $height, $format, $rotateAngle);
        return $this->getImageResponseFactory()->createImageResponse($photoThumb, $format);
    }

    /**
     * @Route("/i/f", name="post_image_full")
     * @ParamConverter("token", class="PhoshMainBundle:Post")
     * @Template()
     */
    public function fullImageAction(Post $post, $format = 'jpeg')
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->assertFalse($post->isExpired());
        }

        $path = $this->getRequest()->get('p');
        $this->assertTrue($post->hasAttachedPhoto($path));

        $rotateAngle = $this->getPhotoStorage()->decodeRotateAngle($this->getRequest()->get('r'));

        $photoThumb = $this->getPhotoStorage()->getPhotoPath($path, $rotateAngle);
        return $this->getImageResponseFactory()->createImageResponse($photoThumb, $format);
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
}