<?php

namespace Phosh\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Phosh\MainBundle\Entity\Post;
use Phosh\MainBundle\Entity\Order;
use Phosh\MainBundle\Entity\Photo;
use Phosh\MainBundle\Form\Type\OrderType;

/**
 * @Route("/{token}", requirements={"token"="\d+"})
 */
class PostController extends BaseController
{
    /**
     * @Route("/", name="post_show_by_token", requirements={"_method"="GET"})
     * @ParamConverter("token", class="PhoshMainBundle:Post")
     * @Template()
     */
    public function showAction(Post $post)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->assertFalse($post->isExpired());
        }

        $order = new Order($post);
        $order->setCustomer($this->getSessionValue('last_order_customer'));
        $order->setComment($this->getSessionValue('last_order_comment'));
        $order->setContact($this->getSessionValue('last_order_contact'));

        $form = $this->createForm(new OrderType(), $order);

        return array(
            'post' => $post,
            'orderForm' => $form->createView(),
        );
    }

    /**
     * @Route("/i/t/{photoId}.jpeg", name="post_photo_thumb", requirements={"photoId"="\d+"})
     * @ParamConverter("token", class="PhoshMainBundle:Post")
     * @Template()
     */
    public function photoThumbAction(Post $post, $photoId, $format = 'jpeg', $width = 100, $height = 100)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->assertFalse($post->isExpired());
        }

        $photo = $this->findPhoto($photoId);
        $this->assertNotNull($photo);
        $this->assertTrue($post->hasProduct($photo->getProduct()));
        
        $imagePath = $this->getPhotoStorage()->getPhotoThumbPath($photo->getPath(), $width, $height, $format, $photo->getRotateAngle());
        return $this->getImageResponseFactory()->createImageResponse($imagePath, $format);
    }

    /**
     * @Route("/i/{photoId}.jpeg", name="post_photo_full", requirements={"photoId"="\d+"})
     * @ParamConverter("token", class="PhoshMainBundle:Post")
     * @Template()
     */
    public function photoFullAction(Post $post, $photoId, $format = 'jpeg')
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->assertFalse($post->isExpired());
        }

        $photo = $this->findPhoto($photoId);
        $this->assertNotNull($photo);
        $this->assertTrue($post->hasProduct($photo->getProduct()));

        $imagePath = $this->getPhotoStorage()->getPhotoPath($photo->getPath(), $format, $photo->getRotateAngle());
        return $this->getImageResponseFactory()->createImageResponse($imagePath, $format);
    }

    /**
     * @param $id
     * @return \Phosh\MainBundle\Entity\Photo|null
     */
    private function findPhoto($id)
    {
        return $this->getRepository(Photo::CLASS_NAME)->find($id);
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