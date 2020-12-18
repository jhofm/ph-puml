<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\CodeProvider;

use Generator;
use Iterator;
use Jhofm\FlysystemIterator\Filter\FilterFactory;
use Jhofm\FlysystemIterator\Plugin\IteratorPlugin;
use Jhofm\PhPuml\Options\Options;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Filesystem;

/**
 * Class CodeProvider
 */
class CodeProvider
{
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
            $iterator = $this->getIterator($directory, $options);
            foreach ($iterator as $file) {
                $path = $directory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file['path']);
                if (!is_readable($path)) {
                    continue;
                }
                yield $path => file_get_contents($path);
            }
        } elseif (is_readable($directory)) {
            yield $directory => file_get_contents($directory);
        }
    }

    /**
     * @param string $directory
     * @param Options $options
     *
     * @return Iterator
     */
    private function getIterator(string $directory, Options $options): Iterator
    {
        $fs = new Filesystem(
            new LocalAdapter(
                $directory,
                LOCK_EX,
                LocalAdapter::SKIP_LINKS
            )
        );
        $fs->addPlugin(new IteratorPlugin());

        $filter = FilterFactory::and(
            FilterFactory::isFile(),
            FilterFactory::pathMatchesRegex('/\.php$/')
        );

        if ($options->{Options::OPTION_EXCLUDE}) {
            foreach ($options->{Options::OPTION_EXCLUDE} as $excludeRegex) {
                if ($excludeRegex !== null && strlen($excludeRegex) > 0)
                $filter = FilterFactory::and(
                    $filter,
                    FilterFactory::not(FilterFactory::pathMatchesRegex($excludeRegex))
                );
            }
        }

        return $fs->createIterator(
            [
                'filter' => $filter,
                'recursive' => true
            ]
        );
    }

}
