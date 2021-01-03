<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Service;

use Jhofm\PhPuml\CodeProvider\CodeProvider;
use Jhofm\PhPuml\NodeParser\NodeParser;
use Jhofm\PhPuml\PhPumlException;
use Jhofm\PhPuml\Relation\RelationInferrer;
use Jhofm\PhPuml\Renderer\PumlRenderer;
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

    /**
     * PhPuml constructor.
     *
     * @param CodeProvider $codeProvider
     * @param NodeParser $nodeParser
     * @param RelationInferrer $relationInferrer
     * @param PumlRenderer $pumlRenderer
     */
    public function __construct(
        CodeProvider $codeProvider,
        NodeParser $nodeParser,
        RelationInferrer $relationInferrer,
        PumlRenderer $pumlRenderer
    ) {
        $this->codeProvider = $codeProvider;
        $this->nodeParser = $nodeParser;
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
        foreach ($this->codeProvider->getCode($input) as $path => $code) {
            foreach ($this->nodeParser->getClassLikes($path, $code) as $classLike) {
                $this->pumlRenderer->addClassLike($classLike);
                $this->pumlRenderer->addRelations($this->relationInferrer->inferRelations($classLike));
            }
        }
        return $this->pumlRenderer->getPuml();
    }
}
