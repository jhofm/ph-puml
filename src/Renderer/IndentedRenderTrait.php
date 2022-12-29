<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Renderer;

trait IndentedRenderTrait
{
    /** @var integer $indentation indentation level */
    private $indentation = 0;

    /** @var string $indentationString string to prepend per level of indentation */
    private $indentationString = '  ';

    private function appendLine(string &$puml, string $line = ''): void
    {
        $puml .= str_repeat($this->indentationString, $this->indentation) . $line . "\n";
    }
}
