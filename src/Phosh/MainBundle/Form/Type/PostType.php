<?php
namespace Phosh\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Phosh\MainBundle\Entity\Post;

class PostType extends AbstractType
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
        ))
        ->add('expiredAt', 'datetime', array(
            'label' => 'Expired at',
            'years' => range(date('Y'), date('Y') + 1),
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
            'date_format' => 'yyyy/MM/dd',
        ));

        $builder->add('token', 'text', array(
            'label' => 'Token',
            'read_only' => true,
        ))->add('regenerateToken', 'checkbox', array(
            'label' => 'Regenerate token',
            'required' => false,
        ));
    }

    public function getName()
    {
        return 'phosh_main_post';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => Post::CLASS_NAME,
        );
    }
}