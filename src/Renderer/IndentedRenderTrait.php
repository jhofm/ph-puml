<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Renderer;

trait IndentedRenderTrait
{
    /** @var int $indentation indentation level */
    private $indentation = 0;
    /** @var string $indentationString string to prepend per level of indentation */
    private $indentationString = '  ';

    /**
     * @param string $puml
     * @param string $line
     */
    private function appendLine(string &$puml, string $line = ''): void
    {
        $puml .= str_repeat($this->indentationString, $this->indentation) . $line . "\n";
    }
}
