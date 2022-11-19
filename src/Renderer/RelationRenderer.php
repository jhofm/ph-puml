<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Renderer;

use Jhofm\PhPuml\Options\Options;
use Jhofm\PhPuml\Options\OptionsException;
use Jhofm\PhPuml\Relation\Relation;

class RelationRenderer
{
    use IndentedRenderTrait;

    /** @var Options  */
    private $options;

    /**
     * RelationRenderer constructor.
     *
     * @param TypeRenderer $typeRenderer
     */
    public function __construct(
        private readonly TypeRenderer $typeRenderer
    ) {
    }

    /**
     * @param array<int, Relation> $relations
     * @throws RendererException
     */
    public function renderRelations(array $relations, Options $options): string
    {
        $this->options = $options;
        $string = '';
        foreach ($relations as $relation) {
            $this->appendLine($string, $this->render($relation));
        }
        if (count($relations) > 0) {
            $this->appendLine($string);
        }
        return $string;
    }

    /**
     * @throws RendererException
     */
    public function render(Relation $relation): string
    {
        try {
            $renderClassNamespaces = $this->options->hasFlag('namespaced-types', 'c');
        } catch (OptionsException $e) {
            throw new RendererException('Unable to determine is class relation namespaces should be rendered.', 1609627636, $e);
        }
        return $this->typeRenderer->render($relation->getSource(), $renderClassNamespaces)
            . $this->renderRelationType($relation, $relation->getSourceQuantifier(), $relation->getTargetQuantifier())
            . $this->typeRenderer->render($relation->getTarget(), $renderClassNamespaces)
            . ($relation->getRole() === null
                ? ''
                : ' : <<' . $relation->getRole() . '>>'
             );
    }

    private function renderRelationType(Relation $relation, ?int $sourceQuantifier, ?int $targetQuantifier): string
    {
        $arrow = '>';
        $line = '..';
        if ($relation->getRelationType() === Relation::RELATION_TYPE_DEPENDENCY) {
            $line = '--';
        } elseif ($relation->getRelationType() === Relation::RELATION_TYPE_EXTENSION) {
            $arrow = '|>';
            $line = '--';
        } elseif ($relation->getRelationType() === Relation::RELATION_TYPE_IMPLEMENTATION) {
            $arrow = '|>';
        }
        return ' '
            . $this->renderQuantifier($sourceQuantifier)
            . $line . $arrow
            . $this->renderQuantifier($targetQuantifier)
            . ' ';
    }

    private function renderQuantifier(?int $sourceQuantifier): string
    {
        if ($sourceQuantifier === null) {
            return '';
        }
        return '"' . ($sourceQuantifier === Relation::QUANTIFIER_ANY ? '*' : (string) $sourceQuantifier) . '"';
    }
}
