<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Relation;

use PhpParser\Node;

class Relation
{
    public const RELATION_TYPE_DEPENDENCY = 'dependency';
    public const RELATION_TYPE_ASSOCIATION = 'association';
    public const RELATION_TYPE_IMPLEMENTATION = 'implementation';
    public const RELATION_TYPE_EXTENSION = 'extension';
    public const QUANTIFIER_ANY = PHP_INT_MAX;

    public function __construct(
        private readonly Node $source,
        private readonly Node $target,
        private readonly string $relationType,
        private readonly ?string $role = null,
        private readonly ?int $sourceQuantifier = null,
        private readonly ?int $targetQuantifier = null
    ) {
    }

    public function getRelationType(): string
    {
        return $this->relationType;
    }

    public function getSource(): Node
    {
        return $this->source;
    }

    public function getTarget(): Node
    {
        return $this->target;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function getSourceQuantifier(): ?int
    {
        return $this->sourceQuantifier;
    }

    public function getTargetQuantifier(): ?int
    {
        return $this->targetQuantifier;
    }
}
