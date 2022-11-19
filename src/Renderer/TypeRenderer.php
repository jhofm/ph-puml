<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Renderer;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;

class TypeRenderer
{
    /** @var array */
    private array $aliases = [];
    /** @var array */
    private array $shortNames = [];

    /**
     * Render a type name
     */
    public function render(Node $type, bool $namespaced = true): string
    {
        $isNullable = $type instanceof NullableType;
        if ($isNullable) {
            /** @var NullableType $type */
            $type = $type->type;
        }
        if (property_exists($type, 'namespacedName')) {
            $type = $type->namespacedName;
        } elseif (property_exists($type, 'name')) {
            $type = $type->name;
        }
        if ($type instanceof Name) {
            $type = $type->toCodeString();
        } else {
            $type = (string) $type;
        }
        // satisfy plantumls craving for backslashes and remove leading backslashes
        $type = ltrim(str_replace('\\', '\\\\', $type), '\\');
        // resolve short name collisions
        if (!$namespaced) {
            if (array_key_exists($type, $this->aliases)) {
                $type = $this->aliases[$type];
            } elseif (strpos($type, '\\') !== false) {
                $type = substr($type, (strrpos($type, '\\') + 1));
            }
        }
        return $type;
    }

    public function addTypeName(Node $node): void
    {
        $fqcn = $this->render($node, true);
        $short = $this->render($node, false);
        if (array_key_exists($short, $this->shortNames)) {
            //TODO: more sparingly build aliases
            $short = $fqcn;
        }
        $this->shortNames[$short] = true;
        $this->aliases[$fqcn] = $short;
    }
}
