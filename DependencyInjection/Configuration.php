<?php

namespace LaxCorp\InnKppBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @package LaxCorp\InnKppBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{

    const ROOT = 'inn_kpp';

    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root($this::ROOT);

        return $treeBuilder;
    }
}