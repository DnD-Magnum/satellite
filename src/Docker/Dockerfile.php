<?php

declare(strict_types=1);

namespace Kiboko\Component\ETL\Satellite\Docker;

use Kiboko\Component\ETL\Satellite\Docker\Dockerfile\LayerInterface;

final class Dockerfile implements \IteratorAggregate, \Countable, FileInterface
{
    /** @var iterable|Dockerfile\LayerInterface[] */
    private iterable $layers;

    public function __construct(Dockerfile\LayerInterface ...$layers)
    {
        $this->layers = $layers;
    }

    public function __toString()
    {
        return implode(PHP_EOL, array_map(function (LayerInterface $layer) {
            return (string) $layer . PHP_EOL;
        }, $this->layers));
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->layers);
    }

    public function count()
    {
        return count($this->layers);
    }

    public function asResource()
    {
        $resource = fopen('php://temp', 'rb+');
        fwrite($resource, (string) $this);
        fseek($resource, 0, SEEK_SET);

        stream_copy_to_stream($resource, STDOUT);
        fseek($resource, 0, SEEK_SET);



        return $resource;
    }

    public function getPath(): string
    {
        return 'Dockerfile';
    }
}