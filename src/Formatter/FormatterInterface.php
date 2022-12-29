<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Formatter;

interface FormatterInterface
{
    public function format(string $puml): string;
}
