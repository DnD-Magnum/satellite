<?php

declare(strict_types=1);

namespace Kiboko\Component\Satellite\Feature\Composer;

use Kiboko\Contract\Configurator\FeatureConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class Configuration implements FeatureConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('composer');

        /** @phpstan-ignore-next-line */
        $builder->getRootNode()
            ->children()
                ->booleanNode('from_local')->defaultFalse()->end()
                ->arrayNode('autoload')
                    ->children()
                        ->arrayNode('psr4')
                            ->useAttributeAsKey('namespace')
                            ->arrayPrototype()
                                ->beforeNormalization()
                                    ->always(function ($data) {
                                        if (array_key_exists('path', $data) && array_key_exists('paths', $data)) {
                                            throw new InvalidConfigurationException('You should either specify the "path" or the "paths" options.');
                                        }

                                        if (array_key_exists('paths', $data)) {
                                            return $data;
                                        }

                                        $data['paths'] = [$data['path']];
                                        unset($data['path']);

                                        return $data;
                                    })
                                ->end()
                                ->children()
                                    ->scalarNode('namespace')->end()
                                    ->scalarNode('path')->end()
                                    ->arrayNode('paths')
                                        ->beforeNormalization()->castToArray()->end()
                                        ->scalarPrototype()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('repositories')
                    ->arrayPrototype()
                        ->children()
                            ->enumNode('type')
                                ->isRequired()
                                ->values(['vcs', 'git', 'git-bitbucket', 'hg-bitbucket', 'github', 'perforce', 'fossil', 'svn', 'hg'])
                            ->end()
                            ->scalarNode('url')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('require')
                    ->scalarPrototype()->end()
                ->end()
            ->end();

        return $builder;
    }
}
