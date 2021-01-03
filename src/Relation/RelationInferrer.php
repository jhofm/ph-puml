<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Relation;

use Jhofm\PhPuml\Renderer\TypeRenderer;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;

/**
 * Class RelationRegistry
 */
class RelationInferrer
{
    /** @var NodeFinder */
    private $nodeFinder;
    /** @var TypeRenderer */
    private $typeRenderer;

    /**
     * RelationInferrer constructor.
     *
     * @param NodeFinder $nodeFinder
     * @param TypeRenderer $typeRenderer
     */
    public function __construct(NodeFinder $nodeFinder, TypeRenderer $typeRenderer)
    {
        $this->nodeFinder = $nodeFinder;
        $this->typeRenderer = $typeRenderer;
    }

    /**
     * @param ClassLike $node
     *
     * @return Relation[]
     */
    public function inferRelations(ClassLike $node): array
    {
        $relations = [];
        foreach ($this->getConstructorArgumentTypes($node) as $type) {
            $relations[] = new Relation($node->namespacedName, $type, Relation::RELATION_TYPE_DEPENDENCY);
        }
        $relationExpressions = $this->getTypesFromNodeTypes(
            $node,
            [
                'Expr_StaticCall',
                'Expr_StaticPropertyFetch',
                'Expr_New',
                'Stmt_Throw'
            ]
        );
        $thrown = [];
        foreach ($relationExpressions['Expr_StaticCall'] as $type) {
            $relations[] = new Relation($node->namespacedName, $type, Relation::RELATION_TYPE_ASSOCIATION, 'uses');
        }
        foreach ($relationExpressions['Expr_StaticPropertyFetch'] as $type) {
            $relations[] = new Relation($node->namespacedName, $type, Relation::RELATION_TYPE_ASSOCIATION, 'uses');
        }
        foreach ($relationExpressions['Stmt_Throw'] as $type) {
            $relations[] = new Relation($node->namespacedName, $type, Relation::RELATION_TYPE_ASSOCIATION, 'throws');
            $thrown[] = (string) $type;
        }
        foreach ($relationExpressions['Expr_New'] as $type) {
            // do not add types created in throw statement as created
            if (!in_array((string) $type, $thrown)) {
                $relations[] = new Relation($node->namespacedName, $type, Relation::RELATION_TYPE_ASSOCIATION, 'creates');
            }
        }
        return $relations;
    }

    /**
     * Get type names of constructor arguments
     *
     * @param Node $node
     *
     * @return Node[] $types
     */
    private function getConstructorArgumentTypes(Node $node): array
    {
        if (!$node instanceof Class_) {
            return [];
        }
        $constructor = $this->nodeFinder->findFirst(
            $node,
            function ($node) {
                return $node instanceof ClassMethod && (string) $node->name === '__construct';
            }
        );
        if ($constructor === null) {
            return [];
        }
        $types = [];
        /** @var ClassMethod $constructor */
        foreach ($constructor->params as $param) {
            if (property_exists($param, 'type') && $param->type instanceof Name) {
                $types[(string) $param->type] = $param->type;
            }
        }
        return $types;
    }

    /**
     * Lookup nodes of multiple types in one finder pass
     *
     * @param ClassLike $node
     * @param array $types classnames of nodes to find
     *
     * @return Node[] $types
     */
    private function getTypesFromNodeTypes(ClassLike $node, array $types): array
    {
        $result = array_flip($types);
        foreach ($result as &$typeResult) {
            $typeResult = [];
        }
        if (!$node instanceof Class_) {
            return $result;
        }
        /** @var Node[] $nodesOfType */
        $nodesOfType = $this->nodeFinder->find(
            $node,
            function (Node $currentNode) use ($result) {
                return array_key_exists($currentNode->getType(), $result);
            }
        );
        foreach ($nodesOfType as $nodeOfType) {
            $type = $this->getNodeTypeName($nodeOfType);
            if ($type !== null) {
                $result[$nodeOfType->getType()][$this->typeRenderer->render($type)] = $type;
            }
        }
        return $result;
    }

    /**
     * @param Node $node
     *
     * @return Name|null
     */
    private function getNodeTypeName(Node $node): ?Name
    {
        if ($node instanceof Node\Stmt\Throw_ && $node->expr instanceof New_) {
            // handle throw new
            $type = $node->expr->class;
            if ($type instanceof Name && !$type->isSpecialClassName()) {
                return $type;
            }
        } elseif ($node instanceof Expr && property_exists($node, 'class')) {
            $type = $node->class;
            // ignore self/parent/static types for now
            if ($type instanceof Name && !$type->isSpecialClassName()) {
                return $type;
            }
        }
        return null;
    }
}
