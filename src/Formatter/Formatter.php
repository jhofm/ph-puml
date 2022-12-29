<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Formatter;

use Jhofm\PhPuml\Options\Options;
use Jhofm\PhPuml\Options\OptionsException;

class Formatter implements FormatterInterface
{
    /**
     * @param iterable<FormatterInterface> $formatters
     */
    public function __construct(
        private readonly Options $options,
        private readonly iterable $formatters
    ) {
    }

    public function format(?string $puml): string
    {
        if ($puml === null) {
            return '';
        }

        try {
            $format = $this->options->get('format');
        } catch (OptionsException $e) {
            throw new FormatterException('Output format not specified.', 1609025248, $e);
        }

        return $this->getFormatterByFormat($format)->format($puml);
    }

    /**
     * @throws FormatterException
     */
    private function getFormatterByFormat(string $format): FormatterInterface
    {
        return $format === 'puml'
            ? $this->getFormatterByClassname(NullFormatterStrategy::class)
            : $this->getFormatterByClassname(PlantUmlFormatterStrategy::class);
    }

    /**
     * @throws FormatterException
     */
    private function getFormatterByClassname(string $class): FormatterInterface
    {
        foreach ($this->formatters as $formatter) {
            if (is_a($formatter, $class)) {
                return $formatter;
            }
        }
        throw new FormatterException(sprintf('Formatter "%s" not found.', $class));
    }
}
