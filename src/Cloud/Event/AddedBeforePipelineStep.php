<?php declare(strict_types=1);

namespace Kiboko\Component\Satellite\Cloud\Event;

final class AddedBeforePipelineStep
{
    public function __construct(
        private string $id,
    ){}

    public function getId(): string
    {
        return $this->id;
    }
}
