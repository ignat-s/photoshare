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
        return array(
            'post' => $post,
        );
    }
}