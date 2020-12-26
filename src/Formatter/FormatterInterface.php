<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Formatter;

/**
 * Interface FormatterInterface
 */
interface FormatterInterface
{
    /**
     * @param string $puml
     *
     * @return string
     */
    public function format(string $puml): string;
}
