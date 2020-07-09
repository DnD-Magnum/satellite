<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Satellite\Docker\Dockerfile;

final class Cmd implements LayerInterface
{
    private string $command;

    public function __construct(string $command)
    {
        $this->command = $command;
    }

    public function __toString()
    {
        return sprintf('CMD %s', $this->command);
    }
}