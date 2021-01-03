<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Renderer;

use Jhofm\PhPuml\Options\Options;
use Jhofm\PhPuml\Options\OptionsException;
use Jhofm\PhPuml\Relation\Relation;

/**
 * Class RelationRenderer
 */
class RelationRenderer
{
    use IndentedRenderTrait;

    /** @var TypeRenderer  */
    private $typeRenderer;
    /** @var Options  */
    private $options;

    /**
     * RelationRenderer constructor.
     *
     * @param TypeRenderer $typeRenderer
     * @param Options $options
     */
    public function __construct(TypeRenderer $typeRenderer, Options $options)
    {
        $this->typeRenderer = $typeRenderer;
        $this->options = $options;
    }

    /**
     * @param array $relations
     *
     * @return string
     * @throws RendererException
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
        } elseif ($relation->getRelationType() === Relation::RELATION_TYPE_EXTENSION) {
            $arrow = '|>';
            $line = '--';
        } elseif ($relation->getRelationType() === Relation::RELATION_TYPE_IMPLEMENTATION) {
            $arrow = '|>';
            $line = '..';
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
