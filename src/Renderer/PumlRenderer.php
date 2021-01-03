<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Renderer;

use Jhofm\PhPuml\Options\Options;
use Jhofm\PhPuml\Options\OptionsException;
use Jhofm\PhPuml\Relation\Relation;
use PhpParser\Node\Stmt\ClassLike;

/**
 * Class PumlRenderer
 */
class PumlRenderer
{
    /** @var string */
    private $buffer = '';
    /** @var ClassLikeRenderer  */
    private $classLikeRenderer;
    /** @var RelationRenderer  */
    private $relationRenderer;
    /** @var Options */
    private $options;

    /**
     * PumlRenderer constructor.
     *
     * @param ClassLikeRenderer $classLikeRenderer
     * @param RelationRenderer $relationRenderer
     * @param Options $options
     */
    public function __construct(
        ClassLikeRenderer $classLikeRenderer,
        RelationRenderer $relationRenderer,
        Options $options
    ) {
        $this->classLikeRenderer = $classLikeRenderer;
        $this->relationRenderer = $relationRenderer;
        $this->options = $options;
    }

    /**
     * @param ClassLike $classLike
     *
     * @throws RendererException
     * @return void
     */
    public function renderClassLike(ClassLike $classLike): void
    {
        $this->buffer .= $this->classLikeRenderer->render($classLike, $this->options);
    }

    /**
     * @param Relation[] $relations
     *
     * @throws RendererException
     * @return void
     */
    public function renderRelations(array $relations): void
    {
        $this->buffer .= $this->relationRenderer->renderRelations($relations, $this->options);
    }

    /**
     * @return string
     * @throws OptionsException
     */
    public function getPuml(): string
    {
        $puml = "@startuml\n\n";
        if ($this->options->hasFlag('namespaced-types', 'c')) {
            $puml .= "set namespaceSeparator \\\\\n\n";
        }
        $puml .= $this->buffer;
        $puml .= "\n@enduml\n";
        return $puml;
    }
}
