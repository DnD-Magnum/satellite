<?php

declare(strict_types=1);

namespace Kiboko\Component\Satellite\Adapter\Tar;

use Kiboko\Component\Satellite;
use Kiboko\Component\Packaging;
use Kiboko\Contract\Packaging as PackagingContract;

final class SatelliteBuilder implements Satellite\SatelliteBuilderInterface
{
    /** @var iterable<string> */
    private iterable $composerRequire;
    private null|PackagingContract\FileInterface|PackagingContract\AssetInterface $composerJsonFile;
    private null|PackagingContract\FileInterface|PackagingContract\AssetInterface $composerLockFile;
    /** @var \AppendIterator<string,PackagingContract\FileInterface> */
    private iterable $files;
    /** @var array<string, list<string>> */
    private array $composerAutoload;

    public function __construct(private string $outputPath)
    {
        $this->composerAutoload = [];
        $this->composerRequire = [];
        $this->composerJsonFile = null;
        $this->composerLockFile = null;
        $this->files = new \AppendIterator();
    }

    public function withComposerPSR4Autoload(string $namespace, string ...$paths): self
    {
        if (!array_key_exists('psr4', $this->composerAutoload)) {
            $this->composerAutoload['psr4'] = [];
        }
        $this->composerAutoload['psr4'][$namespace] = $paths;

        return $this;
    }

    public function withComposerRequire(string ...$package): self
    {
        array_push($this->composerRequire, ...$package);

        return $this;
    }

    public function withComposerFile(
        PackagingContract\FileInterface|PackagingContract\AssetInterface $composerJsonFile,
        null|PackagingContract\FileInterface|PackagingContract\AssetInterface $composerLockFile = null
    ): self {
        $this->composerJsonFile = $composerJsonFile;
        $this->composerLockFile = $composerLockFile;

        return $this;
    }

    public function withFile(
        PackagingContract\FileInterface|PackagingContract\AssetInterface $source,
        ?string $destinationPath = null
    ): self {
        if (!$source instanceof PackagingContract\FileInterface) {
            $source = new Packaging\VirtualFile($source);
        }

        $this->files->append(new \ArrayIterator([
            new Packaging\File($destinationPath, $source),
        ]));

        return $this;
    }

    public function withDirectory(PackagingContract\DirectoryInterface $source, ?string $destinationPath = null): self
    {
        $this->files->append(new \RecursiveIteratorIterator($source, \RecursiveIteratorIterator::SELF_FIRST));

        return $this;
    }

    public function build(): Satellite\SatelliteInterface
    {
        $satellite = new Satellite\Adapter\Tar\Satellite(
            $this->outputPath,
            ...$this->files
        );

        if ($this->composerJsonFile !== null) {
            $satellite->withFile($this->composerJsonFile);

            if ($this->composerLockFile !== null) {
                $satellite->withFile($this->composerLockFile);
            }
        }

        return $satellite;
    }
}
