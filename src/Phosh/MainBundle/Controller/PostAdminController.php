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
        $form = $this->createForm(new PostType(), $post);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $this->getEntityManager()->persist($post);
                $this->addRememberedPostProducts($post);
                $this->getEntityManager()->flush();
                $this->get('session')->setFlash('success', 'Post added');
                return $this->redirect($this->generateUrl('post_show', array('id' => $post->getId())));
            } else {
                $this->get('session')->setFlash('error', 'Post saving error');
            }
        } else {
            $this->resetRememberedPostProducts();
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
     * @Route("/product_add", name="post_product_add")
     * @Template()
     */
    public function productAddAction()
    {
        $productId = $this->getRequest()->get('productId');
        $product = $this->findProduct($productId);
        $this->assertNotNull($product);

        $postId = $this->getRequest()->get('productId');
        if ($postId) {
            $post = $this->findPost($productId);
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
            $this->rememberToAddPostProduct($product);
            $success = true;
            $message = 'Product added';
        }

        return array(
            'success' => $success,
            'message' => $message,
            'product' => $product
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

    private function rememberToAddPostProduct(Product $product)
    {
        $attrName = 'add_products';
        if (!$this->getSession()->has($attrName) || !is_array($this->getSession()->get($attrName))) {
            $this->getSession()->set($attrName, array());
        }

        $attrValue = $this->getSession()->get($attrName);
        $attrValue[$product->getId()] = true;
        $this->getSession()->set($attrName, $attrValue);
    }

    private function addRememberedPostProducts(Post $post)
    {
        $attrName = 'add_products';
        $attrValue = $this->getSession()->get($attrName);
        if ($attrValue || is_array($attrValue)) {
            foreach (array_keys($attrValue) as $productId) {
                $product = $this->findProduct($productId);
                if ($product) {
                    $post->addProduct($product);
                }
            }
        }
    }

    private function resetRememberedPostProducts()
    {
        $attrName = 'add_products';
        $this->getSession()->remove($attrName);
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