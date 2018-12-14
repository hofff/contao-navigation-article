<?php

declare(strict_types=1);

namespace Hofff\Contao\NavigationArticle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('hofff_contao_navigation_article');
        $rootNode
            ->children()
                ->booleanNode('reference_articles_only')
                    ->info(
                        'By default it\'s only possible to select articles marked as references. '
                        . 'By disabling this setting you can select all articles'
                    )
                    ->defaultTrue()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
