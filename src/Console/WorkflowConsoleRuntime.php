<?php declare(strict_types=1);

namespace Kiboko\Component\Satellite\Console;

use Kiboko\Component\Satellite\Console\StateOutput;
use Kiboko\Component\Workflow\SchedulingInterface;
use Kiboko\Contract\Pipeline\PipelineRunnerInterface;
use Kiboko\Contract\Pipeline\RunnableInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

final class WorkflowConsoleRuntime implements WorkflowRuntimeInterface
{
    private StateOutput\Workflow $state;

    public function __construct(
        private ConsoleOutput $output,
        private SchedulingInterface $workflow,
        private PipelineRunnerInterface $pipelineRunner,
    ) {
        $this->state = new StateOutput\Workflow($output);
    }

    public function loadPipeline(string $filename): PipelineRuntimeInterface
    {
        $factory = require $filename;

        $pipeline = new \Kiboko\Component\Pipeline\Pipeline($this->pipelineRunner);
        $this->workflow->job($pipeline);

        return $factory(new Workflow\PipelineConsoleRuntime($this->output, $pipeline, $this->state->withPipeline($filename)));
    }

    public function job(RunnableInterface $job): self
    {
        $this->workflow->job($job);

        return $this;
    }

    public function run(int $interval = 1000): int
    {
        $count = 0;
        $letter = 'A';
        foreach ($this->workflow->walk() as $job) {
            $this->output->writeln(sprintf('%s. Pipeline', $letter++));
//            $count = $job->run($interval);
        }
        return $count;
    }
}
