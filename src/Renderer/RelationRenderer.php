<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Renderer;

use Jhofm\PhPuml\Relation\Relation;

/**
 * Class RelationRenderer
 */
class RelationRenderer
{
    use IndentedRenderTrait;

    /** @var TypeRenderer  */
    private $typeRenderer;

    /**
     * RelationRenderer constructor.
     *
     * @param TypeRenderer $typeRenderer
     */
    public function __construct(TypeRenderer $typeRenderer)
    {
        $this->typeRenderer = $typeRenderer;
    }

    /**
     * @param array $relations
     *
     * @return string
     */
    public function renderRelations(array $relations): string
    {
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
     * @param Relation $relation
     *
     * @return string
     */
    public function render(Relation $relation): string
    {
        return $this->typeRenderer->render($relation->getSource())
            . $this->renderRelationType($relation, $relation->getSourceQuantifier(), $relation->getTargetQuantifier())
            . $this->typeRenderer->render($relation->getTarget())
            . ($relation->getRole() === null ? '' : ' : ' . $relation->getRole());
    }

    /**
     * @param Relation $relation
     * @param int|null $sourceQuantifier
     * @param int|null $targetQuantifier
     *
     * @return string
     */
    private function renderRelationType(Relation $relation, ?int $sourceQuantifier, ?int $targetQuantifier): string
    {
        $arrow = '>';
        $line = '..';
        if ($relation->getRelationType() === Relation::RELATION_TYPE_DEPENDENCY) {
            $line = '--';
        }
        return ' '
            . $this->renderQuantifier($sourceQuantifier)
            . $line . $arrow
            . $this->renderQuantifier($targetQuantifier)
            . ' ';
    }

    /**
     * @param int|null $sourceQuantifier
     *
     * @return string
     */
    private function renderQuantifier(?int $sourceQuantifier): string
    {
        if ($sourceQuantifier === null) {
            return '';
        }
        return '"' . ($sourceQuantifier === Relation::QUANTIFIER_ANY ? '*' : (string) $sourceQuantifier) . '"';
    }
}
