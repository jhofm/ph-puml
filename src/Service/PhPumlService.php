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

class PhPumlService
{
    public function __construct(
        private readonly CodeProvider $codeProvider,
        private readonly NodeParser $nodeParser,
        private readonly ClassLikeRegistry $classLikeRegistry,
        private readonly RelationInferrer $relationInferrer,
        private readonly PumlRenderer $pumlRenderer
    ) {
    }

    /**
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
     * @throws CodeProviderException
     * @throws NodeParserException
     */
    private function addClassLikesToRegistry(string $input): void
    {
        foreach ($this->codeProvider->getCode($input) as $path => $file) {
            foreach ($this->nodeParser->getClassLikes($path, $file->getContents()) as $classLike) {
                $this->classLikeRegistry->addClassLike($classLike);
            }
        }
    }

    /**
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
