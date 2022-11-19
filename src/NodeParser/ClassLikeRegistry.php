<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\NodeParser;

use Jhofm\PhPuml\Renderer\TypeRenderer;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;

class ClassLikeRegistry
{
    /** @var array<string, ClassLike> */
    private $classLikes = [];

    /**
     * ClassLikeRegistry constructor.
     *
     * @param TypeRenderer $typeRenderer
     */
    public function __construct(
        private readonly TypeRenderer $typeRenderer
    ) {
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

    public function getClassLikes(): array
    {
        return $this->classLikes;
    }
}
