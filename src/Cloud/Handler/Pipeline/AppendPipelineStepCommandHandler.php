<?php

declare(strict_types=1);

namespace Kiboko\Component\Satellite\Cloud\Handler\Pipeline;

use Gyroscops\Api;
use Kiboko\Component\Satellite\Cloud;
use Kiboko\Component\Satellite\Cloud\DTO\Probe;

final class AppendPipelineStepCommandHandler
{
    public function __construct(
        private Api\Client $client
    ) {}

    public function __invoke(Cloud\Command\Pipeline\AppendPipelineStepCommand $command): Cloud\Event\AppendedPipelineStep
    {
        try {
            /** @var \stdClass $result */
            $result = $this->client->appendPipelineStepPipelineCollection(
                (new Api\Model\PipelineAppendPipelineStepCommandInput())
                    ->setPipeline((string) $command->pipeline)
                    ->setCode((string) $command->step->code)
                    ->setLabel($command->step->label)
                    ->setConfiguration($command->step->config)
                    ->setProbes($command->step->probes->map(
                        fn (Probe $probe) => (new Api\Model\Probe())->setCode($probe->code)->setLabel($probe->label)
                    ))
            );
        } catch (Api\Exception\AppendPipelineStepPipelineCollectionBadRequestException $exception) {
            throw new Cloud\AppendPipelineStepFailedException(
                'Something went wrong while trying to append a pipeline step. Maybe your client is not up to date, you may want to update your Gyroscops client.',
                previous: $exception
            );
        } catch (Api\Exception\AppendPipelineStepPipelineCollectionUnprocessableEntityException $exception) {
            throw new Cloud\AppendPipelineStepFailedException(
                'Something went wrong while trying to append a pipeline step. It seems the data you sent was invalid, please check your input.',
                previous: $exception
            );
        }

        if ($result === null) {
            // TODO: change the exception message, it doesn't give enough details on how to fix the issue
            throw new Cloud\AppendPipelineStepFailedException('Something went wrong while trying to append a pipeline step.');
        }

        return new Cloud\Event\AppendedPipelineStep($result->id);
    }
}
