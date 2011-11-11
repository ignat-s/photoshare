<?php
namespace Phosh\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Phosh\MainBundle\Form\Model\ConfigModel;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('orderCreatedToEmail', 'email', array(
            'label' => 'Order created "to" E-mail',
            'attr' => array(
                'class' => 'xxlarge',
            ),
        ))->add('indexPageContent', 'textarea', array(
            'label' => 'Index page content',
            'attr' => array(
                'class' => 'xxlarge',
            ),
            'required' => false
        ));
    }

    public function getName()
    {
        return 'phosh_main_config';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => ConfigModel::CLASS_NAME,
        );
    }
}