<?php

declare(strict_types=1);

namespace Kiboko\Component\Satellite\Cloud\Handler\Pipeline;

use Gyroscops\Api;
use Kiboko\Component\Satellite\Cloud;

final class AppendPipelineStepCommandHandler
{
    public function __construct(
        private Api\Client $client
    ) {}

    public function __invoke(Cloud\Command\Pipeline\AppendPipelineStepCommand $command): Cloud\Event\AppendedPipelineStep
    {
        $result = $this->client->appendPipelineStepPipelineCollection(
            (new Api\Model\PipelineAppendPipelineStepCommandInput())
                ->setPipeline((string) $command->pipeline)
                ->setCode((string) $command->step->code)
                ->setLabel($command->step->label)
                ->setConfiguration($command->step->config)
                ->setProbes(
                    array_map(
                        fn (Cloud\DTO\Probe $probe) => (new Api\Model\Probe())->setCode($probe->code)->setLabel($probe->label),
                        $command->step->probes->toArray()
                    )
                ),
        );

        if ($result === null) {
            throw throw new \RuntimeException('Something went wrong wile appending a step into the pipeline.');
        }

        return new Cloud\Event\AppendedPipelineStep($result->id);
    }
}
