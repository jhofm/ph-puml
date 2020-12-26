<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Service;

use Jhofm\PhPuml\CodeProvider\CodeProvider;
use Jhofm\PhPuml\PhPumlException;
use Jhofm\PhPuml\Relation\RelationInferrer;
use Jhofm\PhPuml\Renderer\ClassLikeRenderer;
use Jhofm\PhPuml\Renderer\RelationRenderer;
use PhpParser\Error as ParserError;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\Parser;

/**
 * Class PhPuml
 */
class PhPuml
{
    private const PUML_HEADER = <<<EOT
@startuml
        
set namespaceSeparator \\\\ 


EOT;

    private const PUML_FOOTER = "@enduml\n";

    /** @var Parser */
    private $parser;
    /** @var NodeTraverser */
    private $namespaceTraverser;
    /** @var CodeProvider */
    private $codeProvider;
    /** @var NodeFinder */
    private $nodeFinder;
    /** @var ClassLikeRenderer  */
    private $classLikeRenderer;
    /** @var RelationInferrer  */
    private $relationInferrer;
    /** @var RelationRenderer  */
    private $relationRenderer;

    /**
     * PhPuml constructor.
     *
     * @param CodeProvider $codeProvider
     * @param NodeFinder $nodeFinder
     * @param NodeTraverser $namespaceTraverser
     * @param Parser $parser
     * @param RelationInferrer $relationInferrer
     * @param ClassLikeRenderer $classLikeRenderer
     * @param RelationRenderer $relationRenderer
     */
    public function __construct(
        CodeProvider $codeProvider,
        NodeFinder $nodeFinder,
        NodeTraverser $namespaceTraverser,
        Parser $parser,
        RelationInferrer $relationInferrer,
        ClassLikeRenderer $classLikeRenderer,
        RelationRenderer $relationRenderer
    ) {
        $this->codeProvider = $codeProvider;
        $this->parser = $parser;
        $this->namespaceTraverser = $namespaceTraverser;
        $this->nodeFinder = $nodeFinder;
        $this->classLikeRenderer = $classLikeRenderer;
        $this->relationInferrer = $relationInferrer;
        $this->relationRenderer = $relationRenderer;
    }

    /**
     * @param string $input
     *
     * @return string
     * @throws PhPumlException
     */
    public function generatePuml(string $input): string
    {
        $puml = self::PUML_HEADER;
        $codeFiles = $this->codeProvider->getCode($input);
        foreach ($codeFiles as $path => $code) {
            try {
                $nodes = $this->parser->parse($code);
            } catch (ParserError $e) {
                throw new PhPumlException(sprintf('Parser error in file "%s".', $path), 1608810962, $e);
            }
            // resolve namespaces for all types
            $nodes = $this->namespaceTraverser->traverse($nodes);
            /** @var ClassLike[] $classLikes */
            $classLikes = $this->nodeFinder->findInstanceOf($nodes, ClassLike::class);
            foreach ($classLikes as $classLike) {
                $puml .= $this->classLikeRenderer->render($classLike);
                $puml .= $this->relationRenderer->renderRelations($this->relationInferrer->inferRelations($classLike));
            }
        }
        $puml .= self::PUML_FOOTER;
        return $puml;
    }
}
