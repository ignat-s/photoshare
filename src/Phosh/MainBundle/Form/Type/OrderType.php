<?php
namespace Phosh\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

use Phosh\MainBundle\Entity\Order;
use Phosh\MainBundle\Entity\Post;
use Phosh\MainBundle\Entity\Product;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        /**
         * @var $order \Phosh\MainBundle\Entity\Order
         */
        $order = $builder->getData();
        $products = $order->getPost()->getProducts();

        $builder->add('customer', 'text', array(
            'label' => 'Name',
            'attr' => array(
                'class' => 'xxlarge',
            ),
        ))->add('contact', 'textarea', array(
            'label' => 'Contacts',
            'attr' => array(
                'class' => 'xxlarge',
            ),
            'required' => false,
        ))->add('comment', 'textarea', array(
            'label' => 'Comments',
            'attr' => array(
                'class' => 'xxlarge',
            ),
            'required' => false,
        ));
        
        if (count($products)) {
            $products->add('products', 'entity', array(
                'class' => Product::CLASS_NAME,
                'property' => 'title',
                'label' => 'Products',
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'query_builder' => function(EntityRepository $repository) use ($products)
                {
                    $productIds = array(0);
                    foreach ($products as $product) {
                        $productIds[] = $product->getId();
                    }
                    $qb = $repository->createQueryBuilder('p');
                    $qb->where($qb->expr()->in('p.id', $productIds))
                    ->orderBy('p.title');

                    return $qb;
                },
            ));
        }
    }

    public function getName()
    {
        return 'phosh_main_order';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => Order::CLASS_NAME,
        );
    }
}