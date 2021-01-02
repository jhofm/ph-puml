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
     * @param Node|null $type
     * @param bool $namespaced
     *
     * @return string
     */
    public function render(?Node $type, bool $namespaced = true): string
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
            $type = $type->toCodeString();
        } else {
            $type = (string) $type;
        }

        // shorten type for short mode if namespaced
        if (!$namespaced && strpos($type, '\\') !== false) {
            $type = substr($type, (strrpos($type, '\\') + 1));
        }

        $type = str_replace('\\', '\\\\', $type);
        if ($isNullable) {
            $type .= ' = null';
        }
        return $type;
    }
}
