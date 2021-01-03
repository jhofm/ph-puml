<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Renderer;

use Jhofm\PhPuml\Relation\Relation;
use PhpParser\Node\Stmt\ClassLike;

/**
 * Class PumlRenderer
 */
class PumlRenderer
{
    private const PUML_HEADER = "@startuml\n\nset namespaceSeparator \\\\\n\n";
    private const PUML_FOOTER = "\n@enduml\n";

    /** @var string */
    private $buffer = '';

    /**
     * PumlRenderer constructor.
     *
     * @param ClassLikeRenderer $classLikeRenderer
     * @param RelationRenderer $relationRenderer
     */
    public function __construct(ClassLikeRenderer $classLikeRenderer, RelationRenderer $relationRenderer)
    {
        $this->classLikeRenderer = $classLikeRenderer;
        $this->relationRenderer = $relationRenderer;
    }

    /**
     * @param ClassLike $classLike
     *
     * @throws RendererException
     * @return void
     */
    public function addClassLike(ClassLike $classLike): void
    {
        $this->buffer .= $this->classLikeRenderer->render($classLike);
    }

    /**
     * @param Relation[] $relations
     *
     * @throws RendererException
     * @return void
     */
    public function addRelations(array $relations): void
    {
        $this->buffer .= $this->relationRenderer->renderRelations($relations);
    }

    /**
     * @return string
     */
    public function getPuml(): string
    {
        return self::PUML_HEADER
            . $this->buffer
            . self::PUML_FOOTER;
    }
}
