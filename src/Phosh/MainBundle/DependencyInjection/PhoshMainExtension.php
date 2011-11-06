<?php

namespace Phosh\MainBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

class PhoshMainExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('config.xml');
        $loader->load('mailer.xml');
        $loader->load('twig.xml');
        $loader->load('http.xml');

        $container->setParameter('phosh.storage_dir', $config['storage_dir']);
        $container->setParameter('phosh.thumbs_dir', $config['thumbs_dir']);
        $container->setParameter('phosh_main.required_config_attrs', $config['required_config_attrs']);
    }

    public function getAlias()
    {
        return 'phosh_main';
    }
}