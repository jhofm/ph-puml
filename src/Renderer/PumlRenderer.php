<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Renderer;

use Jhofm\PhPuml\Options\Options;
use Jhofm\PhPuml\Options\OptionsException;
use Jhofm\PhPuml\Relation\Relation;
use PhpParser\Node\Stmt\ClassLike;

class PumlRenderer
{
    /** @var string */
    private $buffer = '';

    public function __construct(
        private readonly ClassLikeRenderer $classLikeRenderer,
        private readonly RelationRenderer $relationRenderer,
        private readonly Options $options
    ) {
    }

    /**
     * @param ClassLike $classLike
     * @throws RendererException
     */
    public function renderClassLike(ClassLike $classLike): void
    {
        $this->buffer .= $this->classLikeRenderer->render($classLike, $this->options);
    }

    /**
     * @param Relation[] $relations
     * @throws RendererException
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
