<?php declare(strict_types=1);

namespace Kiboko\Component\Satellite\Feature\State;

use Kiboko\Component\Satellite\Feature\State;
use Kiboko\Contract\Configurator;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception as Symfony;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class Service implements Configurator\FactoryInterface
{
    private Processor $processor;
    private ConfigurationInterface $configuration;
    private ExpressionLanguage $interpreter;

    public function __construct(
        ?ExpressionLanguage $interpreter = null,
    )
    {
        $this->processor = new Processor();
        $this->configuration = new Configuration();
        $this->interpreter = $interpreter ?? new ExpressionLanguage();
    }

    public function configuration(): ConfigurationInterface
    {
        return $this->configuration;
    }

    /**
     * @throws Configurator\ConfigurationExceptionInterface
     */
    public function normalize(array $config): array
    {
        try {
            return $this->processor->processConfiguration($this->configuration, $config);
        } catch (Symfony\InvalidTypeException|Symfony\InvalidConfigurationException $exception) {
            throw new Configurator\InvalidConfigurationException($exception->getMessage(), 0, $exception);
        }
    }

    public function validate(array $config): bool
    {
        try {
            if ($this->processor->processConfiguration($this->configuration, $config)) {
                return true;
            }
        } catch (\Exception) {
        }

        return false;
    }

    public function compile(array $config): Repository
    {
        $builder = new Builder\State();
        $repository = new Repository($builder);

        try {
            if (!array_key_exists('destinations', $config)
                || count($config['destinations']) <= 0
            ) {
                return $repository;
            }

            foreach ($config['destinations'] as $destination) {
                if (array_key_exists('rabbitmq', $destination)) {
                    $factory = new State\Factory\RabbitMQFactory($this->interpreter);

                    $rabbitmqRepository = $factory->compile($destination['rabbitmq']);

                    $repository->merge($rabbitmqRepository);
                    $builder->withState($rabbitmqRepository->getBuilder()->getNode());
                }
            }

            return $repository;
        } catch (Symfony\InvalidTypeException|Symfony\InvalidConfigurationException $exception) {
            throw new Configurator\InvalidConfigurationException(
                message: $exception->getMessage(),
                previous: $exception
            );
        }
    }
}
