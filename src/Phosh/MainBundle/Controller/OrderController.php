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
class OrderController extends BaseController
{
    /**
     * @Route("/create_order", name="order_create", requirements={"_method"="POST"})
     * @ParamConverter("token", class="PhoshMainBundle:Post")
     * @Template()
     */
    public function createAction(Post $post)
    {
        $order = new Order($post);
        $form = $this->createForm(new OrderType(), $order);
        $form->bindRequest($this->getRequest());

        $this->setSessionValue('last_order_customer', $order->getCustomer());
        $this->setSessionValue('last_order_comment', $order->getComment());
        $this->setSessionValue('last_order_contact', $order->getContact());

        if (!$post->isExpired()) {
            if ($form->isValid()) {
                $this->getEntityManager()->persist($order);
                $this->getEntityManager()->flush();

                $this->getMailer()->sendOrderCreatedEmailMessage($order);

                $success = true;
                $message = 'Thank you! Your order is recieved!';

            } else {
                $success = false;
                $message = 'Please check your order form.';
            }
        } else {
            $success = false;
            $message = 'Post is expired!';
        }

        if ($success) {
            return $this->redirect($this->generateUrl('order_received', array('token' => $post->getToken(), 'orderNumber' => $order->getNumber())));
        } else {
            $this->get('session')->setFlash('error', $message);
            return $this->redirect($this->generateUrl('post_show_by_token', array('token' => $post->getToken())));
        }
    }

    /**
     * @Route("/order_received/{orderNumber}", name="order_received", requirements={"orderNumber"="\d+"})
     * @ParamConverter("token", class="PhoshMainBundle:Post")
     * @Template()
     */
    public function receivedAction(Post $post, $orderNumber)
    {
        $order = $this->findOrder($orderNumber);
        $this->assertNotNull($order);
        return array(
            'order' => $order,
        );
    }

    /**
     * @return \Phosh\MainBundle\Mailer\Mailer
     */
    private function getMailer()
    {
        return $this->get('phosh_main.mailer');
    }

    private function findOrder($id)
    {
        return $this->getRepository(Order::CLASS_NAME)->find($id);
    }
}