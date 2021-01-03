<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\NodeParser;

use PhpParser\Error as ParserError;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\Parser as PhpParser;

/**
 * Class NodeParser
 */
class NodeParser
{
    /** @var PhpParser  */
    private $phpParser;
    /** @var NodeFinder  */
    private $nodeFinder;
    /** @var NodeTraverser */
    private $namespaceTraverser;

    /**
     * NodeParser constructor.
     *
     * @param PhpParser $phpParser
     * @param NodeFinder $nodeFinder
     * @param NodeTraverser $namespaceTraverser
     */
    public function __construct(PhpParser $phpParser, NodeFinder $nodeFinder, NodeTraverser $namespaceTraverser)
    {
        $this->phpParser = $phpParser;
        $this->nodeFinder = $nodeFinder;
        $this->namespaceTraverser = $namespaceTraverser;
    }

    /**
     * @param string $path
     * @param string $code
     *
     * @return Node[]
     * @throws NodeParserException
     */
    public function getClassLikes(string $path, string $code): array
    {
        try {
            $nodes = $this->phpParser->parse($code);
        } catch (ParserError $e) {
            throw new NodeParserException(sprintf('Parser error in file "%s".', $path), 1608810962, $e);
        }
        // resolve namespaces for all types
        $nodes = $this->namespaceTraverser->traverse($nodes);
        /** @var ClassLike[] $classLikes */
        return $this->nodeFinder->findInstanceOf($nodes, ClassLike::class);
    }
}
