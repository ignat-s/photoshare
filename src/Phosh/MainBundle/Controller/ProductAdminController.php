<?php

namespace Phosh\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Phosh\MainBundle\Entity\Product;
use Phosh\MainBundle\Entity\Photo;
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
     * @Route("/", name="product_index", requirements={"_method"="GET"})
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
     * @Route("/{id}/", name="product_show", requirements={"_method"="GET", "id" = "\d+"})
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
     * @Route("/create/", name="product_create")
     * @Template()
     */
    public function createAction()
    {
        $product = new Product();
        $product->setOwner($this->getCurrentUser());

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $this->addRememberedProductPhotos($product);
            $form = $this->createForm(new ProductType(), $product);
            $form->bindRequest($request);

            if ($form->isValid()) {
                $this->getEntityManager()->persist($product);
                $this->getEntityManager()->flush();
                $this->get('session')->setFlash('success', 'Product added');
                return $this->redirect($this->generateUrl('product_show', array('id' => $product->getId())));
            } else {
                $this->get('session')->setFlash('error', 'Product saving error');
            }
        } else {
            $this->resetRememberedProductPhotos();
            $form = $this->createForm(new ProductType(), $product);
        }

        return array(
            'product' => $product,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/edit/", name="product_edit", requirements={"id" = "\d+"})
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
     * @Route("/{id}/delete/", name="product_delete", requirements={"id" = "\d+"})
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
     * @Route("/search/", name="product_search", defaults={"_format"="json"})
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

        $query = $qb->orderBy('product.title', 'ASC')
                ->setParameter('titleMask', \strtolower(sprintf('%%%s%%', $term)))
                ->getQuery();

        return array(
            'products' => $query->execute(),
        );
    }

    /**
     * @Route("/{id}/photo_add", name="product_photo_add")
     * @ParamConverter("id", class="PhoshMainBundle:Product")
     * @Template()
     */
    public function photoAddAction($id)
    {
        if ($this->getRequest()->getRequestFormat() == 'html') {
            return $this->forward('PhoshMainBundle:ProductAdmin:show', array('id' => $id));
        }

        $type = $this->getRequest()->get('type');
        $path = $this->getRequest()->get('path');

        if ($id) {
            $product = $this->findProduct($id);
            $this->assertNotNull($product);
            if ($type == 'file') {
                $rotateAngle = $this->getPhotoStorage()->decodeRotateAngle($this->getRequest()->get('rotateAngle'));
                $photo = new Photo();
                $photo->setPath($path);
                $photo->setRotateAngle($rotateAngle);

                if ($product->hasPhoto($photo)) {
                    $success = false;
                    $message = 'Product already has this photo';
                } else {
                    $product->addPhoto($photo);
                    $this->getEntityManager()->flush();

                    $success = true;
                    $message = 'Photo added';
                }
            } else if ($type == 'dir') {
                $success = false;
                $message = 'Adding directory is not implemented yet';
            } else {
                $this->fail(null, 400);
            }
        } else {
            if ($type == 'file') {
                $rotateAngle = $this->getPhotoStorage()->decodeRotateAngle($this->getRequest()->get('rotateAngle'));
                $photo = new Photo();
                $photo->setPath($path);
                $photo->setRotateAngle($rotateAngle);
                if (!$this->hasRememberedProductPhoto($photo)) {
                    $this->setRememberToAddProductPhoto($photo);
                    $success = true;
                    $message = 'Photo added';
                } else {
                    $success = false;
                    $message = 'Product already has this photo';
                }


            } else if ($type == 'dir') {
                $success = false;
                $message = 'Adding directory is not implemented yet';
            } else {
                $this->fail(null, 400);
            }
        }

        return array(
            'success' => $success,
            'message' => $message,
            'photo' => isset($photo) ? $photo : null
        );
    }

    /**
     * @Route("/{id}/photo_remove", name="product_photo_remove")
     * @ParamConverter("id", class="PhoshMainBundle:Product")
     * @Template()
     */
    public function photoRemoveAction($id)
    {
        if ($this->getRequest()->getRequestFormat() == 'html') {
            return $this->forward('PhoshMainBundle:ProductAdmin:show', array('id' => $id));
        }
        $photoIds = $this->getRequest()->get('photos');

        if ($id) {
            $product = $this->findProduct($id);
            $this->assertNotNull($product);
            
            $em = $this->getEntityManager();
            $photos = $this->findProductPhotos($product, $photoIds);

            $this->assertTrue(count($photos) == count($photoIds));

            foreach ($photos as $photo) {
                $em->remove($photo);
            }
            $em->flush();
        } else {
            foreach ($photoIds as $id) {
                $this->unsetRememberToAddPostProduct($id);
            }
        }
        $success = true;
        $message = 'Product photos removed';

        return array(
            'success' => $success,
            'message' => $message
        );
    }

    private function setRememberToAddProductPhoto(Photo $photo)
    {
        $attrName = 'remembered_photos';
        $attrValue = $this->getSessionArrayValue($attrName);
        $attrValue[$photo->getHash()] = $photo;
        $this->setSessionValue($attrName, $attrValue);
    }

    private function hasRememberedProductPhoto(Photo $photo)
    {
        $attrName = 'remembered_photos';
        $attrValue = $this->getSessionArrayValue($attrName);
        return isset($attrValue[$photo->getHash()]);
    }

    private function unsetRememberToAddPostProduct($photo)
    {
        $attrName = 'remembered_photos';
        $attrValue = $this->getSessionArrayValue($attrName);
        $hash = $photo instanceof Photo ? $photo->getHash() : $photo;
        unset($attrValue[$hash]);
        $this->setSessionValue($attrName, $attrValue);
    }

    private function addRememberedProductPhotos(Product $product)
    {
        $attrName = 'remembered_photos';
        foreach ($this->getSessionArrayValue($attrName) as $photo) {
            $product->addPhoto($photo);
        }
    }

    private function resetRememberedProductPhotos()
    {
        $attrName = 'remembered_photos';
        $this->removeSessionValue($attrName);
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

    /**
     * @param \Phosh\MainBundle\Entity\Product $product
     * @param array $ids
     * @return \Phosh\MainBundle\Entity\Photo[]
     */
    private function findProductPhotos(Product $product, $ids)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $photos = $qb->select('photo')
            ->from('PhoshMainBundle:Photo', 'photo')
            ->where($qb->expr()->in('photo.id', $ids))
            ->andWhere('photo.product = :product')
            ->setParameter('product', $product)
            ->getQuery()->getResult();

        return $photos;
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
     * @return \Phosh\MainBundle\PhotoStorage
     */
    public function getPhotoStorage()
    {
        return $this->get('phosh.photo_storage');
    }
}