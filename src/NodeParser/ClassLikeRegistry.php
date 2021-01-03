<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\NodeParser;

use Jhofm\PhPuml\Renderer\TypeRenderer;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;

/**
 * Class ClassLikeRegistry
 */
class ClassLikeRegistry
{
    /** @var array */
    private $classLikes = [];
    /** @var TypeRenderer  */
    private $typeRenderer;

    /**
     * ClassLikeRegistry constructor.
     *
     * @param TypeRenderer $typeRenderer
     */
    public function __construct(TypeRenderer $typeRenderer)
    {
        $this->typeRenderer = $typeRenderer;
    }

    /**
     * @param ClassLike $classLike
     *
     * @return void
     */
    public function addClassLike(ClassLike $classLike): void
    {
        $this->classLikes[$this->typeRenderer->render($classLike, true)] = $classLike;
        $this->typeRenderer->addTypeName($classLike);
    }

    /**
     * @param Node $node
     *
     * @return bool
     */
    public function has(Node $node): bool
    {
        return array_key_exists($this->typeRenderer->render($node, true), $this->classLikes);
    }

    /**
     * @return array
     */
    public function getClassLikes(): array
    {
        return $this->classLikes;
    }
}
