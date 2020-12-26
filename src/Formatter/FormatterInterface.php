<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Formatter;

/**
 * Interface FormatterInterface
 */
interface FormatterInterface
{
    public function format(string $puml): string;
}
