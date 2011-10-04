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