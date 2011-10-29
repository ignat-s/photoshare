<?php

namespace Phosh\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Phosh\MainBundle\Entity\Product;
use Phosh\MainBundle\Entity\Post;
use Phosh\MainBundle\Form\Type\ProductType;
use Phosh\MainBundle\Pager\Pager;

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
        $pager = $this->createPager();

        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
                ->from('PhoshMainBundle:Product', 'product');

        $select = clone $queryBuilder;
        $select->select('product')
                ->orderBy('product.title', 'ASC')
                ->setFirstResult($pager->getOffset())
                ->setMaxResults($pager->getPerPage());
        $totalCount = clone $queryBuilder;
        $totalCount->select('count(product.id)');

        $products = $select->getQuery()->execute();
        $pager->setCount($totalCount->getQuery()->getSingleScalarResult());

        return array(
            'products' => $products,
            'pager' => $pager,
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
        $ignorePostProducts = $this->getRequest()->get('ignorePostProducts');
        $ignoreRememberedProducts = $this->getRequest()->get('ignoreRememberedProducts');

        $qb = $this->getEntityManager()->createQueryBuilder()
                ->select('product')
                ->from('PhoshMainBundle:Product', 'product')
                ->where('lower(product.title) like :titleMask');

        if ($ignorePostProducts) {
            $qb->andWhere($qb->expr()->notIn('product.id', $this->findPostProductIds($ignorePostProducts)));
        }

        if ($ignoreRememberedProducts) {
            $rememberedProductIds = array_keys($this->getSessionArrayValue('remembered_products'));
            if ($rememberedProductIds) {
                $qb->andWhere($qb->expr()->notIn('product.id', $rememberedProductIds));
            }
        }

        $products = $qb->orderBy('product.title', 'ASC')
                ->setParameter('titleMask', \mb_strtolower(sprintf('%%%s%%', $term)))
                ->getQuery()->execute();

        return array(
            'products' => $products,
        );
    }

    /**
     * @param $postId
     * @return \Phosh\MainBundle\Entity\Product[]
     */
    private function findPostProductIds($postId)
    {
        $queryResult = $this->getEntityManager()->createQueryBuilder()
                ->select('product.id')
                ->from('PhoshMainBundle:Post', 'post')
                ->leftJoin('post.products', 'product')
                ->where('post.id = :postId')
                ->setParameter('postId', $postId)
                ->getQuery()->getScalarResult();

        $result = array();
        foreach ($queryResult as $row) {
            $result[] = $row['id'];
        }

        return $result;
    }
}