<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\CodeProvider;

use Generator;
use Jhofm\PhPuml\Options\Options;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\StorageAttributes;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Traversable;

class CodeProvider
{
    private const RESULT_KEY_PATH = 'path';
    private const RESULT_KEY_CONTENT = 'content';

    public function __construct(
        private readonly Finder $finder,
        private readonly Options $options
    ) {
    }

    /**
     * @return Traversable<string, SplFileInfo>
     * @throws CodeProviderException
     */
    public function getCode(string $directory): Traversable
    {
        $directory = realpath($directory);
        if ($directory === false) {
            throw new CodeProviderException(sprintf('Code path "%s" not found.', $directory));
        }
        $include = array_filter((array) $this->options->include);
        $exclude = array_filter((array) $this->options->exclude);
        return $this->finder->in($directory)->files()
            ->path($include)
            ->notPath($exclude)
            ->getIterator();
    }
}
