<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\NodeParser;

use PhpParser\Error as ParserError;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\Parser as PhpParser;

class NodeParser
{
    public function __construct(
        private readonly PhpParser $phpParser,
        private readonly NodeFinder $nodeFinder,
        private readonly NodeTraverser $namespaceTraverser
    ) {
    }

    /**
     * @return array<int, ClassLike>
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
