<?php

namespace Phosh\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Phosh\MainBundle\Entity\Product;
use Phosh\MainBundle\Form\Type\ProductType;

/**
 * @Route("/admin/products")
 * @Secure(roles="ROLE_ADMIN")
 */
class ProductAdminController extends BaseController
{
    /**
     * @Route("", name="product_index", requirements={"_method"="GET"})
     * @Template()
     */
    public function indexAction()
    {
        $products = $this->getEntityManager()->createQueryBuilder()
                ->select('product')
                ->from('PhoshMainBundle:Product', 'product')
                ->orderBy('product.title', 'ASC')
                ->getQuery()->execute();

        return array(
            'products' => $products,
        );
    }

    /**
     * @Route("/{id}", name="product_show", requirements={"_method"="GET", "id" = "\d+"})
     * @ParamConverter("id", class="PhoshMainBundle:Product")
     * @Template()
     */
    public function showAction(Product $product)
    {
        return array(
            'product' => $product,
        );
    }

    /**
     * @Route("/create", name="product_create", requirements={"id" = "\d+"})
     * @Template()
     */
    public function createAction()
    {
        $product = new Product();
        $product->setOwner($this->getCurrentUser());
        $form = $this->createForm(new ProductType(), $product);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $this->getEntityManager()->persist($product);
                $this->getEntityManager()->flush();
                $this->get('session')->setFlash('success', 'Product added');
                return $this->redirect($this->generateUrl('product_show', array('id' => $product->getId())));
            } else {
                $this->get('session')->setFlash('error', 'Product saving error');
            }
        }

        return array(
            'product' => $product,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/edit", name="product_edit", requirements={"id" = "\d+"})
     * @ParamConverter("id", class="PhoshMainBundle:Product")
     * @Template()
     */
    public function editAction(Product $product)
    {
        $form = $this->createForm(new ProductType(), $product);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $this->getEntityManager()->flush();
                $this->get('session')->setFlash('success', 'Product updated');
                return $this->redirect($this->generateUrl('product_show', array('id' => $product->getId())));
            } else {
                $this->get('session')->setFlash('error', 'Product saving error');
            }
        }

        return array(
            'product' => $product,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/delete", name="product_delete", requirements={"id" = "\d+"})
     * @ParamConverter("id", class="PhoshMainBundle:Product")
     */
    public function deleteAction(Product $product)
    {
        try {
            $this->getEntityManager()->remove($product);
            $this->getEntityManager()->flush();

            $this->get('session')->setFlash('success', 'Product deleted');
            return $this->redirect($this->generateUrl('product_index'));
        } catch (\PDOException $e) {
            $this->get('session')->setFlash('error', $e->getMessage());
            return $this->redirect($this->generateUrl('product_show', array('id' => $product->getId())));
        }
    }

    /**
     * @Route("/search", name="product_search", defaults={"_format"="json"})
     * @Template()
     */
    public function searchAction()
    {
        $term = $this->getRequest()->get('term');

        $products = $this->getEntityManager()->createQueryBuilder()
                ->select('product')
                ->from('PhoshMainBundle:Product', 'product')
                ->where('lower(product.title) like :titleMask')
                ->orderBy('product.title', 'ASC')
                ->setParameter('titleMask', \mb_strtolower(sprintf('%%%s%%', $term)))
                ->getQuery()->execute();

        return array(
            'products' => $products,
        );
    }
}