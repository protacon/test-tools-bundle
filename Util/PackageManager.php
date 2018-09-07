<?php
declare(strict_types = 1);

namespace Protacon\Bundle\TestToolsBundle\Util;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use function array_map;
use function iterator_to_array;

/**
 * Class PackageManager
 *
 * @package Protacon\Bundle\TestToolsBundle\Util
 */
class PackageManager
{
    /**
     * @var string
     */
    private $resourcePath;

    /**
     * @var string
     */
    private $targetPath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * PackageManager constructor.
     *
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {
        $this->resourcePath = __DIR__ . '/../Resources/package/';
        $this->targetPath = $projectDir . '/vendor-bin/';
        $this->projectDir = $projectDir;
        $this->filesystem = new Filesystem();
    }

    /**
     * @return array
     */
    public function listPackages(): array
    {
        $finder = (new Finder())->directories()->depth(0)->in($this->resourcePath);

        $packages = [];

        foreach ($finder as $directory) {
            /** @var SplFileInfo $directory */
            $packages[] = $directory->getFilename();
        }

        return $packages;
    }

    /**
     * @param string $package
     */
    public function addPackage(string $package): void
    {
        $sourcePath = $this->resourcePath . $package;
        $targetPath = $this->targetPath . $package;

        $iterator = (new Finder())->files()->depth(0)->ignoreDotFiles(false)->in($sourcePath);

        $copy = function (SplFileInfo $file) use ($sourcePath, $targetPath) {
            $this->filesystem->copy(
                $sourcePath . '/' . $file->getFilename(),
                $targetPath . '/' . $file->getFilename()
            );
        };

        array_map($copy, iterator_to_array($iterator));

        $this->configurePackage($package);
    }

    /**
     * @return void
     */
    public function addReadme(): void
    {
        $this->filesystem->copy(
            $this->resourcePath . '/README.md',
            $this->targetPath . '/README.md'
        );
    }

    /**
     * @param string $package
     */
    private function configurePackage(string $package): void
    {
        $configDir = $this->resourcePath . $package . '/config/';

        if (!$this->filesystem->exists($configDir)) {
            return;
        }

        $iterator = (new Finder())->files()->ignoreDotFiles(false)->in($configDir);

        $copy = function (SplFileInfo $file) use ($configDir) {
            $this->filesystem->copy(
                $configDir . $file->getFilename(),
                $this->projectDir . $file->getFilename()
            );
        };

        array_map($copy, iterator_to_array($iterator));
    }
}