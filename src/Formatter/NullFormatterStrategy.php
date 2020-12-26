<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Formatter;

/**
 * Class NullFormatterStrategy
 */
class NullFormatterStrategy implements FormatterInterface
{
    public function format(string $puml): string
    {
        return $puml;
    }
}
