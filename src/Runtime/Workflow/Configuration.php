<?php

declare(strict_types=1);

namespace Kiboko\Component\Satellite\Runtime\Workflow;

use Kiboko\Component\Satellite;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Kiboko\Component\SatelliteToolbox;

final class Configuration implements Satellite\NamedConfigurationInterface
{
    public function getName(): string
    {
        return 'workflow';
    }

    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('workflow');

        /** @phpstan-ignore-next-line */
        $builder->getRootNode()
            ->children()
                ->append((new SatelliteToolbox\Configuration\ImportConfiguration())->getConfigTreeBuilder()->getRootNode())
                ->arrayNode('jobs')
                    ->arrayPrototype()
                        ->children()
                            ->append((new Satellite\Runtime\Pipeline\Configuration())->getConfigTreeBuilder()->getRootNode())
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
