<?php

namespace Phosh\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Phosh\MainBundle\Entity\Post;
use Phosh\MainBundle\Entity\Product;
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
        $this->makeUniquePostToken($post);
        $post->setOwner($this->getCurrentUser());


        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $this->addRememberedPostProducts($post);
            $form = $this->createForm(new PostType(), $post);
            $form->bindRequest($request);

            if ($form->isValid()) {
                $this->getEntityManager()->persist($post);
                $this->getEntityManager()->flush();
                $this->get('session')->setFlash('success', 'Post added');
                return $this->redirect($this->generateUrl('post_show', array('id' => $post->getId())));
            } else {
                $this->get('session')->setFlash('error', 'Post saving error');
            }
        } else {
            $now = new \DateTime();
            $now->modify(sprintf('+1 day -%d minute - %d second', $now->format('i'), $now->format('s')));
            $post->setExpiredAt($now);
            $this->resetRememberedPostProducts();
            $form = $this->createForm(new PostType(), $post);
        }

        return array(
            'post' => $post,
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
        $form = $this->createForm(new PostType(), $post);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                if ($post->isRegenerateToken()) {
                    $this->makeUniquePostToken($post);
                }
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


    /**
     * @Route("/{id}/product_add/{productId}", name="post_product_add")
     * @Template()
     */
    public function productAddAction($id, $productId)
    {
        $product = $this->findProduct($productId);
        $this->assertNotNull($product);

        if ($id) {
            $post = $this->findPost($id);
            $this->assertNotNull($post);
            if ($post->hasProduct($product)) {
                $success = false;
                $message = 'Product already exists';
            } else {
                $post->addProduct($product);
                $this->getEntityManager()->flush();

                $success = true;
                $message = 'Product added';
            }
        } else {
            $this->setRememberToAddPostProduct($product);
            $success = true;
            $message = 'Product added';
        }

        return array(
            'success' => $success,
            'message' => $message,
            'product' => $product
        );
    }

    /**
     * @Route("/{id}/product_remove/{productId}", name="post_product_remove", requirements={"id" = "\d+", "productId" = "\d+"})
     * @ParamConverter("id", class="PhoshMainBundle:Post")
     * @Template()
     */
    public function productRemoveAction($id, $productId)
    {
        $product = $this->findProduct($productId);
        $this->assertNotNull($product);

        if ($id) {
            $post = $this->findPost($id);
            $this->assertNotNull($post);

            $this->assertTrue($post->hasProduct($product));

            $post->removeProduct($product);

            $this->getEntityManager()->flush();
        } else {
            $this->unsetRememberToAddPostProduct($product);
        }

        $success = true;
        $message = 'Product removed';

        return array(
            'success' => $success,
            'message' => $message
        );
    }

    public function makeUniquePostToken(Post $post)
    {
        $post->regenerateToken();
        $existedPost = $this->findPostByToken($post->getToken());
        if ($existedPost) {
            $this->makeUniquePostToken($post);
        }
    }

    private function setRememberToAddPostProduct(Product $product)
    {
        $attrName = 'remembered_products';
        $attrValue = $this->getSessionArrayValue($attrName);
        $attrValue[$product->getId()] = true;
        $this->setSessionValue($attrName, $attrValue);
    }

    private function unsetRememberToAddPostProduct(Product $product)
    {
        $attrName = 'remembered_products';
        $attrValue = $this->getSessionArrayValue($attrName);
        unset($attrValue[$product->getId()]);
        $this->setSessionValue($attrName, $attrValue);
    }

    private function addRememberedPostProducts(Post $post)
    {
        $attrName = 'remembered_products';
        foreach (array_keys($this->getSessionArrayValue($attrName)) as $productId) {
            $product = $this->findProduct($productId);
            if ($product) {
                $post->addProduct($product);
            }
        }
    }

    private function resetRememberedPostProducts()
    {
        $attrName = 'remembered_products';
        $this->removeSessionValue($attrName);
    }

    /**
     * @param $id
     * @return \Phosh\MainBundle\Entity\Product|null
     */
    private function findProduct($id)
    {
        return $this->getRepository(Product::CLASS_NAME)->find($id);
    }

    /**
     * @param $id
     * @return \Phosh\MainBundle\Entity\Post|null
     */
    private function findPost($id)
    {
        return $this->getRepository(Post::CLASS_NAME)->find($id);
    }

    /**
     * @param $token
     * @return \Phosh\MainBundle\Entity\Post|null
     */
    private function findPostByToken($token)
    {
        return $this->getRepository(Post::CLASS_NAME)->findOneByToken($token);
    }
}