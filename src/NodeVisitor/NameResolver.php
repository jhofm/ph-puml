<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\NodeVisitor;

use PhpParser\BuilderHelpers;
use PhpParser\Comment\Doc;
use PhpParser\Node\NullableType;
use PhpParser\NodeVisitor\NameResolver as OriginalNameResolver;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

/**
 * Class NameResolver
 *
 * Extended from original NameResolver in order to resolve types in var comments
 */
class NameResolver extends OriginalNameResolver
{
    public function __construct()
    {
        parent::__construct(
            null,
            ['preserveOriginalNames' => true]
        );
    }

    public function enterNode(Node $node) {
        if ($node instanceof Stmt\Namespace_) {
            $this->nameContext->startNamespace($node->name);
        } elseif ($node instanceof Stmt\Use_) {
            foreach ($node->uses as $use) {
                $this->addAlias($use, $node->type, null);
            }
        } elseif ($node instanceof Stmt\GroupUse) {
            foreach ($node->uses as $use) {
                $this->addAlias($use, $node->type, $node->prefix);
            }
        } elseif ($node instanceof Stmt\Class_) {
            if (null !== $node->extends) {
                $node->extends = $this->resolveClassName($node->extends);
            }

            foreach ($node->implements as &$interface) {
                $interface = $this->resolveClassName($interface);
            }

            $this->resolveAttrGroups($node);
            if (null !== $node->name) {
                $this->addNamespacedName($node);
            }
        } elseif ($node instanceof Stmt\Interface_) {
            foreach ($node->extends as &$interface) {
                $interface = $this->resolveClassName($interface);
            }

            $this->resolveAttrGroups($node);
            $this->addNamespacedName($node);
        } elseif ($node instanceof Stmt\Trait_) {
            $this->resolveAttrGroups($node);
            $this->addNamespacedName($node);
        } elseif ($node instanceof Stmt\Function_) {
            $this->resolveSignature($node);
            $this->resolveAttrGroups($node);
            $this->addNamespacedName($node);
        } elseif ($node instanceof Stmt\ClassMethod
            || $node instanceof Expr\Closure
            || $node instanceof Expr\ArrowFunction
        ) {
            $this->resolveSignature($node);
            $this->resolveAttrGroups($node);
        } elseif ($node instanceof Stmt\Property) {
            if (null !== $node->type) {
                $node->type = $this->resolveType($node->type);
            }
            $this->resolveAttrGroups($node);
            $this->resolveAttributes($node);
        } elseif ($node instanceof Stmt\Const_) {
            foreach ($node->consts as $const) {
                $this->addNamespacedName($const);
            }
        } else if ($node instanceof Stmt\ClassConst) {
            $this->resolveAttrGroups($node);
        } elseif ($node instanceof Expr\StaticCall
            || $node instanceof Expr\StaticPropertyFetch
            || $node instanceof Expr\ClassConstFetch
            || $node instanceof Expr\New_
            || $node instanceof Expr\Instanceof_
        ) {
            if ($node->class instanceof Name) {
                $node->class = $this->resolveClassName($node->class);
            }
        } elseif ($node instanceof Stmt\Catch_) {
            foreach ($node->types as &$type) {
                $type = $this->resolveClassName($type);
            }
        } elseif ($node instanceof Expr\FuncCall) {
            if ($node->name instanceof Name) {
                $node->name = $this->resolveName($node->name, Stmt\Use_::TYPE_FUNCTION);
            }
        } elseif ($node instanceof Expr\ConstFetch) {
            $node->name = $this->resolveName($node->name, Stmt\Use_::TYPE_CONSTANT);
        } elseif ($node instanceof Stmt\TraitUse) {
            foreach ($node->traits as &$trait) {
                $trait = $this->resolveClassName($trait);
            }

            foreach ($node->adaptations as $adaptation) {
                if (null !== $adaptation->trait) {
                    $adaptation->trait = $this->resolveClassName($adaptation->trait);
                }

                if ($adaptation instanceof Stmt\TraitUseAdaptation\Precedence) {
                    foreach ($adaptation->insteadof as &$insteadof) {
                        $insteadof = $this->resolveClassName($insteadof);
                    }
                }
            }
        }

        return null;
    }

    private function addAlias(Stmt\UseUse $use, $type, Name $prefix = null) {
        // Add prefix for group uses
        $name = $prefix ? Name::concat($prefix, $use->name) : $use->name;
        // Type is determined either by individual element or whole use declaration
        $type |= $use->type;

        $this->nameContext->addAlias(
            $name, (string) $use->getAlias(), $type, $use->getAttributes()
        );
    }

    /** @param Stmt\Function_|Stmt\ClassMethod|Expr\Closure $node */
    private function resolveSignature($node) {
        foreach ($node->params as $param) {
            $param->type = $this->resolveType($param->type);
            $this->resolveAttrGroups($param);
        }
        $node->returnType = $this->resolveType($node->returnType);
    }

    private function resolveType($node) {
        if ($node instanceof Name) {
            return $this->resolveClassName($node);
        }
        if ($node instanceof NullableType) {
            $node->type = $this->resolveType($node->type);
            return $node;
        }
        if ($node instanceof Node\UnionType) {
            foreach ($node->types as &$type) {
                $type = $this->resolveType($type);
            }
            return $node;
        }
        return $node;
    }

    /**
     * Addition for PHPumlGen: Resolve types in var doc comments
     *
     * @param $node
     */
    private function resolveAttributes(Stmt\Property $node)
    {
        $attributes = $node->getAttributes();
        if (!array_key_exists('comments', $attributes)) {
            return;
        }

        foreach ($attributes['comments'] as &$comment) {
            if (!$comment instanceof Doc) {
                continue;
            }
            $match = [];
            if (preg_match('~@var\s+([^\s*]+)~m', (string)$comment, $match)) {
                $type = $match[1];
                //TODO use builder?
                $type = BuilderHelpers::normalizeType($type);
                if ($type instanceof NullableType) {
                    $type = $type->type;
                }
                if ($type instanceof Name) {
                    $type = $this->resolveClassName($type);
                }
                $text = preg_replace('~(?<=@var )[^\s*]+~m', (string) $type, (string) $comment);
                $comment = new Doc(
                    $text,
                    $comment->getStartLine(),
                    $comment->getStartFilePos(),
                    $comment->getStartTokenPos(),
                    $comment->getEndLine(),
                    $comment->getEndFilePos(),
                    $comment->getEndTokenPos()
                );
            }
        }
        $node->setAttribute('comments', $attributes['comments']);
    }
}
