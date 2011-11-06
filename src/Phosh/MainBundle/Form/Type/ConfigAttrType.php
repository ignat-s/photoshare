<?php
namespace Phosh\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Phosh\MainBundle\Entity\ConfigAttr;

class ConfigAttrType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'label' => 'Name',
            'attr' => array(
                'class' => 'xxlarge',
            ),
        ))->add('title', 'text', array(
            'label' => 'Title',
            'attr' => array(
                'class' => 'xxlarge',
            ),
        ))->add('value', 'text', array(
            'label' => 'Value',
            'attr' => array(
                'class' => 'xxlarge',
            ),
            'required' => false,
        ));
    }

    public function getName()
    {
        return 'phosh_main_config_attr';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => ConfigAttr::CLASS_NAME,
        );
    }
}