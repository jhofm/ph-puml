<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Relation;

use PhpParser\Node;

/**
 * Class Relation
 */
class Relation
{
    public const RELATION_TYPE_DEPENDENCY = 'dependency';
    public const RELATION_TYPE_ASSOCIATION = 'association';

    public const QUANTIFIER_ANY = PHP_INT_MAX;

    /** @var Node */
    private $source;
    /** @var Node */
    private $target;
    /** @var string */
    private $relationType;
    /** @var string|null */
    private $role;
    /** @var int|null */
    private $sourceQuantifier;
    /** @var int|null */
    private $targetQuantifier;

    /**
     * Relation constructor.
     *
     * @param Node $source
     * @param Node $target
     * @param string $relationType
     * @param string|null $role
     * @param int|null $sourceQuantifier
     * @param int|null $targetQuantifier
     */
    public function __construct(
        Node $source,
        Node $target,
        string $relationType,
        ?string $role = null,
        ?int $sourceQuantifier = null,
        ?int $targetQuantifier = null
    ) {
        $this->source = $source;
        $this->target = $target;
        $this->relationType = $relationType;
        $this->role = $role;
        $this->sourceQuantifier = $sourceQuantifier;
        $this->targetQuantifier = $targetQuantifier;
    }

    /**
     * @return string
     */
    public function getRelationType(): string
    {
        return $this->relationType;
    }

    /**
     * @return Node
     */
    public function getSource(): Node
    {
        return $this->source;
    }

    /**
     * @return Node
     */
    public function getTarget(): Node
    {
        return $this->target;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @return int|null
     */
    public function getSourceQuantifier(): ?int
    {
        return $this->sourceQuantifier;
    }

    /**
     * @return int|null
     */
    public function getTargetQuantifier(): ?int
    {
        return $this->targetQuantifier;
    }
}
