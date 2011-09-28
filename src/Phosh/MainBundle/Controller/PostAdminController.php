<?php

namespace Phosh\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Phosh\MainBundle\Entity\Post;
use Phosh\MainBundle\Form\Type\PostType;

/**
 * @Route("/admin/posts")
 * @Secure(roles="ROLE_ADMIN")
 */
class PostAdminController extends BaseController
{
    /**
     * @Route("", name="post_index", requirements={"_method"="GET"})
     * @Template()
     */
    public function indexAction()
    {
        $posts = $this->getEntityManager()->createQueryBuilder()
                ->select('post')
                ->from('PhoshMainBundle:Post', 'post')
                ->orderBy('post.createdAt', 'DESC')
                ->getQuery()->execute();

        return array(
            'posts' => $posts,
        );
    }

    /**
     * @Route("/{id}", name="post_show", requirements={"_method"="GET", "id" = "\d+"})
     * @ParamConverter("id", class="PhoshMainBundle:Post")
     * @Template()
     */
    public function showAction(Post $post)
    {
        return array(
            'post' => $post,
        );
    }

    /**
     * @Route("/create", name="post_create", requirements={"id" = "\d+"})
     * @Template()
     */
    public function createAction()
    {
        $post = new Post();
        $post->setOwner($this->getCurrentUser()->getUsername());
        $form = $this->createForm(new PostType(PostType::CREATE_TYPE), $post);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $this->getEntityManager()->persist($post);
                $this->getEntityManager()->flush();
                $this->get('session')->setFlash('success', 'Post added');
                return $this->redirect($this->generateUrl('post_show', array('id' => $post->getId())));
            } else {
                $this->get('session')->setFlash('error', 'Post saving error');
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/edit", name="post_edit", requirements={"id" = "\d+"})
     * @ParamConverter("id", class="PhoshMainBundle:Post")
     * @Template()
     */
    public function editAction(Post $post)
    {
        $form = $this->createForm(new PostType(PostType::EDIT_TYPE), $post);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $this->getEntityManager()->flush();
                $this->get('session')->setFlash('success', 'Post updated');
                return $this->redirect($this->generateUrl('post_show', array('id' => $post->getId())));
            } else {
                $this->get('session')->setFlash('error', 'Post saving error');
            }
        }

        return array(
            'post' => $post,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/delete", name="post_delete", requirements={"id" = "\d+"})
     * @ParamConverter("id", class="PhoshMainBundle:Post")
     * @Template()
     */
    public function deleteAction(Post $post)
    {
        try {
            $this->getEntityManager()->remove($post);
            $this->getEntityManager()->flush();

            $this->get('session')->setFlash('success', 'Post deleted');
            return $this->redirect($this->generateUrl('post_index'));
        } catch (\PDOException $e) {
            $this->get('session')->setFlash('error', $e->getMessage());
            return $this->redirect($this->generateUrl('post_show', array('id' => $post->getId())));
        }
    }
}