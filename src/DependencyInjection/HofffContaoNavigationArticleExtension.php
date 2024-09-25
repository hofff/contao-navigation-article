<?php

declare(strict_types=1);

namespace Hofff\Contao\NavigationArticle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class HofffContaoNavigationArticleExtension extends Extension
{
    /** {@inheritDoc} */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config'),
        );

        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter(
            'hofff_contao_navigation_article.reference_articles_only',
            $config['reference_articles_only'],
        );

        $loader->load('listener.xml');
    }
}
