<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Service;

use Jhofm\PhPuml\CodeProvider\CodeProvider;
use Jhofm\PhPuml\CodeProvider\CodeProviderException;
use Jhofm\PhPuml\NodeParser\ClassLikeRegistry;
use Jhofm\PhPuml\NodeParser\NodeParser;
use Jhofm\PhPuml\NodeParser\NodeParserException;
use Jhofm\PhPuml\Options\OptionsException;
use Jhofm\PhPuml\PhPumlException;
use Jhofm\PhPuml\Relation\RelationInferrer;
use Jhofm\PhPuml\Renderer\PumlRenderer;
use Jhofm\PhPuml\Renderer\RendererException;
use PhpParser\NodeFinder;

/**
 * Class PhPumlService
 */
class PhPumlService
{
    /** @var CodeProvider */
    private $codeProvider;
    /** @var NodeFinder */
    /** @var RelationInferrer  */
    private $relationInferrer;
    /** @var PumlRenderer  */
    private $pumlRenderer;
    /** @var NodeParser */
    private $nodeParser;
    /** @var ClassLikeRegistry */
    private $classLikeRegistry;

    /**
     * PhPuml constructor.
     *
     * @param CodeProvider $codeProvider
     * @param NodeParser $nodeParser
     * @param ClassLikeRegistry $classLikeRegistry
     * @param RelationInferrer $relationInferrer
     * @param PumlRenderer $pumlRenderer
     */
    public function __construct(
        CodeProvider $codeProvider,
        NodeParser $nodeParser,
        ClassLikeRegistry $classLikeRegistry,
        RelationInferrer $relationInferrer,
        PumlRenderer $pumlRenderer
    ) {
        $this->codeProvider = $codeProvider;
        $this->nodeParser = $nodeParser;
        $this->classLikeRegistry = $classLikeRegistry;
        $this->relationInferrer = $relationInferrer;
        $this->pumlRenderer = $pumlRenderer;
    }

    /**
     * @param string $input
     *
     * @return string
     * @throws PhPumlException
     */
    public function generatePuml(string $input): string
    {
        $this->addClassLikesToRegistry($input);
        return $this->render();
    }

    /**
     * Add classlikes to registry to determine included types and resolve name conflicts of types
     *
     * @param string $input
     *
     * @return void
     * @throws CodeProviderException
     * @throws NodeParserException
     */
    private function addClassLikesToRegistry(string $input): void
    {
        foreach ($this->codeProvider->getCode($input) as $path => $code) {
            foreach ($this->nodeParser->getClassLikes($path, $code) as $classLike) {
                $this->classLikeRegistry->addClassLike($classLike);
            }
        }
    }

    /**
     * @return string
     * @throws OptionsException
     * @throws RendererException
     */
    private function render(): string
    {
        foreach ($this->classLikeRegistry->getClassLikes() as $classLike) {
            $this->pumlRenderer->renderClassLike($classLike);
            $this->pumlRenderer->renderRelations($this->relationInferrer->inferRelations($classLike));
        }
        return $this->pumlRenderer->getPuml();
    }
}
