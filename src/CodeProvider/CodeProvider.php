<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\CodeProvider;

use Generator;
use Jhofm\PhPuml\Options\Options;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\StorageAttributes;
use Traversable;

/**
 * Class CodeProvider
 */
class CodeProvider
{
    private const RESULT_KEY_PATH = 'path';
    private const RESULT_KEY_CONTENT = 'content';

    /**
     * @param string $directory
     *
     * @return Generator
     * @throws CodeProviderException
     */
    public function getCode(string $directory, Options $options): Generator {

        $directory = realpath($directory);
        if ($directory === false) {
            throw new CodeProviderException(sprintf('Code path "%s" not found.', $directory));
        }

        if (is_dir($directory)) {
            try {
                $iterator = $this->getIterator($directory, $options);
                foreach ($iterator as $file) {
                    yield $file[self::RESULT_KEY_PATH] => $file[self::RESULT_KEY_CONTENT];
                }
            } catch (FilesystemException $e) {
                throw new CodeProviderException('Error reading code files.', 1608933590, $e);
            }
        } elseif (is_readable($directory)) {
            yield $directory => file_get_contents($directory);
        }
    }

    /**
     * @param string $directory
     * @param Options $options
     *
     * @return Traversable
     * @throws FilesystemException
     */
    private function getIterator(string $directory, Options $options): Traversable
    {
        $fs = new Filesystem(
            new LocalFilesystemAdapter(
                $directory,
                null,
                LOCK_EX,
                LocalFilesystemAdapter::DISALLOW_LINKS
            )
        );
        return $fs->listContents('.', true)
            ->filter(
                function (StorageAttributes $attributes) use ($options) {
                    if (!$attributes->isFile()) {
                        return false;
                    }
                    // at least one inclusion rule must match
                    $match = false;
                    foreach ($options->{Options::OPTION_INCLUDE} as $includeRegex) {
                        if ($includeRegex !== null && preg_match($includeRegex, $attributes->path())) {
                            $match = true;
                            break;
                        }
                    }
                    // no exclusion rule may match
                    if ($match === true) {
                        foreach ($options->{Options::OPTION_EXCLUDE} as $excludeRegex) {
                            if ($excludeRegex !== null && preg_match($excludeRegex, $attributes->path())) {
                                return false;
                            }
                        }
                        return true;
                    }
                    return false;
                }
            )->map(
                function (StorageAttributes $attributes) use ($options, $directory, $fs) {
                    return [
                        self::RESULT_KEY_PATH => $directory . '/' . $attributes->path(),
                        self::RESULT_KEY_CONTENT => $fs->read($attributes->path())
                    ];
                }
            );
    }

}
