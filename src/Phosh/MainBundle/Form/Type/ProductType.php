<?php
namespace Phosh\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Phosh\MainBundle\Entity\Product;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('title', 'text', array(
            'label' => 'Title',
            'attr' => array(
                'class' => 'span12',
            ),
        ))
        ->add('description', 'textarea', array(
            'label' => 'Description',
            'attr' => array(
                'class' => 'span12',
            ),
            'required' => false
        ));
    }

    public function getName()
    {
        return 'phosh_main_post';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => Product::CLASS_NAME,
        );
    }
}