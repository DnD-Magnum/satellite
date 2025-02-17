<?php

declare(strict_types=1);

namespace Kiboko\Component\Satellite\Adapter\Tar;

use Kiboko\Component\Packaging\TarArchive;
use Kiboko\Component\Satellite\SatelliteInterface;
use Kiboko\Contract\Packaging;
use Psr\Log\LoggerInterface;

final class Satellite implements SatelliteInterface
{
    /** @var string[] */
    private array $imageTags;
    /** @var iterable<Packaging\DirectoryInterface|Packaging\FileInterface> */
    private iterable $files;
    private iterable $dependencies;

    public function __construct(
        private string $outputPath,
        Packaging\FileInterface|Packaging\DirectoryInterface ...$files
    ) {
        $this->imageTags = [];
        $this->files = $files;
        $this->dependencies = [];
    }

    public function addTags(string ...$imageTags): self
    {
        array_push($this->imageTags, ...$imageTags);

        return $this;
    }

    public function withFile(Packaging\FileInterface|Packaging\DirectoryInterface ...$files): self
    {
        array_push($this->files, ...$files);

        return $this;
    }

    public function dependsOn(string ...$dependencies): self
    {
        array_push($this->dependencies, ...$dependencies);

        return $this;
    }

    public function build(
        LoggerInterface $logger,
    ): void {
        $archive = new TarArchive(...$this->files);

        mkdir(dirname($this->outputPath), 0755, true);

        $stream = \gzopen($this->outputPath, 'wb');
        assert($stream !== false, new \ErrorException(error_get_last()['message'], filename: error_get_last()['file'], line: error_get_last()['line'],));
        stream_copy_to_stream($archive->asResource(), $stream);
        \gzclose($stream);
    }
}
