<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Renderer;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;

/**
 * Class TypeRenderer
 */
class TypeRenderer
{
    /**
     * Render a type name
     * 
     * @param ?Node $type
     *
     * @return string
     */
    public function render(?Node $type): string
    {
        if ($type === null) {
            return 'UNKNOWN-TYPE';
        }

        $isNullable = $type instanceof NullableType;
        if ($isNullable) {
            /** @var NullableType $type */
            $type = $type->type;
        }
        if ($type instanceof Name) {
            $string = $type->toCodeString();
        } else {
            $string = (string) $type;
        }
        $string = str_replace('\\', '\\\\', $string);
        if ($isNullable) {
            $string .= ' = null';
        }
        return $string;
    }
}
