<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Formatter;

/**
 * Class NullFormatterStrategy
 */
class NullFormatterStrategy implements FormatterInterface
{
    /**
     * My eyes! The formatter does nothing!
     *
     * @param string $puml
     *
     * @return string
     */
    public function format(string $puml): string
    {
        return $puml;
    }
}
