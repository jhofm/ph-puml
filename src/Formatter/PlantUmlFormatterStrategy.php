<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Formatter;

use Jhofm\PhPuml\Options\Options;
use Jhofm\PhPuml\Options\OptionsException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class PlantUmlFormatterStrategy implements FormatterInterface
{
    public function __construct(
        private readonly Options $options
    ) {
    }

    /**
     * @throws FormatterException
     */
    public function format(string $puml): string
    {
        $proc = new Process(['java', '-jar', $this->getPlantUmlJarPath(), '-pipe', $this->getPlantUmlParameterForFormat($this->options->format)]);
        $proc->setInput($puml);
        try {
            $proc->mustRun();
        } catch (ProcessFailedException $e) {
            throw new FormatterException('PlantUML run failed.', 1609025808, $e);
        } catch (ProcessTimedOutException $e) {
            throw new FormatterException('PlantUML run timed out.', 1609025809, $e);
        }
        return $proc->getOutput();
    }

    /**
     * Get plantuml.jar cli parameter for an output format
     */
    private function getPlantUmlParameterForFormat(string $format): string
    {
        return sprintf('-t%s', $format);
    }

    /**
     * @throws FormatterException
     */
    private function getPlantUmlJarPath(): string
    {
        try {
            $path = realpath($this->options->get('plantuml-path'));
        } catch (OptionsException $e) {
            throw new FormatterException('Error determining plantuml-path parameter.', 1609059884, $e);
        }
        if ($path === false) {
            throw new FormatterException(
                'Format unavailable, plantuml.jar not found. Either provide a valid path via the plantuml-path parameter or install the optional composer package jawira/plantuml.'
            );
        }
        return $path;
    }
}
