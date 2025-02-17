<?php

namespace Kiboko\Component\Satellite\Cloud\DTO;

interface PipelineInterface
{
    public function code(): string;

    public function label(): string;

    public function steps(): StepList;

    public function autoload(): Autoload;
}
