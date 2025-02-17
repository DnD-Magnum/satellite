<?php

declare(strict_types=1);

namespace Kiboko\Component\Satellite\Runtime\Workflow;

use Kiboko\Component\Satellite;
use Kiboko\Contract\Configurator;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

final class Configuration implements Configurator\RuntimeConfigurationInterface
{
    /** @var array<string, Configurator\PluginConfigurationInterface> */
    private iterable $plugins = [];
    /** @var array<string, Configurator\FeatureConfigurationInterface> */
    private iterable $features = [];

    private Satellite\Runtime\Pipeline\Configuration $pipelineConfiguration;

    public function __construct()
    {
        $this->pipelineConfiguration = new Satellite\Runtime\Pipeline\Configuration();
    }

    public function addPlugin(string $name, Configurator\PluginConfigurationInterface $plugin): self
    {
        $this->pipelineConfiguration->addPlugin(
            $name,
            $plugin
        );

        return $this;
    }

    public function addFeature(string $name, Configurator\FeatureConfigurationInterface $feature): self
    {
        $this->pipelineConfiguration->addFeature(
            $name,
            $feature
        );

        return $this;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('workflow');

        /** @phpstan-ignore-next-line */
        $builder->getRootNode()
            ->children()
                ->append((new Satellite\DependencyInjection\Configuration\ServicesConfiguration())->getConfigTreeBuilder()->getRootNode())
                ->arrayNode('expression_language')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('name')->end()
                ->arrayNode('jobs')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->end()
                            ->append($this->pipelineConfiguration->getConfigTreeBuilder()->getRootNode())
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
