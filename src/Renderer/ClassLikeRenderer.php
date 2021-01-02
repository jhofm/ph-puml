<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Renderer;

use Jhofm\PhPuml\Options\Options;
use Jhofm\PhPuml\Options\OptionsException;
use PhpParser\Comment;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\VarLikeIdentifier;

/**
 * Class ClassLikeRenderer
 */
class ClassLikeRenderer
{
    use IndentedRenderTrait;

    /** @var array $typeMap node statement class -> puml keyword  */
    private $typeMap = [
        Class_::class => 'class',
        Interface_::class => 'interface',
        Trait_::class => 'class'
    ];
    /** @var TypeRenderer  */
    private $typeRenderer;
    /** @var Options  */
    private $options;

    /**
     * ClassLikeRenderer constructor.
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
     * Render a ClassLike Node as PlantUML
     *
     * @param ClassLike $node
     *
     * @return string
     * @throws RendererException
     */
    public function render(ClassLike $node): string
    {
        $puml = '';
        $this->indentation = 0;
        $this->appendLine($puml, $this->renderClassLikeHeader($node));
        ++$this->indentation;
        $puml .= $this->renderProperties($node);
        $puml .= $this->renderMethods($node);
        --$this->indentation;
        $this->appendLine($puml, '}');
        $this->appendLine($puml);
        return $puml;
    }

    /**
     * @param ClassLike $node
     *
     * @return string
     * @throws RendererException
     */
    private function renderClassLikeHeader(ClassLike $node): string
    {
        $puml = '';
        if ($node instanceof Class_ && $node->isAbstract() || $node instanceof Trait_) {
            $puml .= 'abstract ';
        }
        $puml .= $this->typeMap[get_class($node)] . ' ';
        $className = $this->typeRenderer->render(
            property_exists($node, 'namespacedName') ? $node->namespacedName : null,
            $this->renderNamespace($node)
        );
        $puml .= $className . ' ';
        if ($node instanceof Class_ && $node->isFinal()) {
            $puml .= '<<leaf>> ';
        }
        if ($node instanceof Trait_) {
            $puml .= '<<trait>> ';
        }
        $puml .= $this->renderExtends($node);
        $puml .= $this->renderImplements($node);
        $puml .= '{';
        return $puml;
    }

    /**
     * @param ClassLike $node
     *
     * @return string
     * @throws RendererException
     */
    private function renderProperties(ClassLike $node): string
    {
        $puml = '';
        $properties = $node->getProperties();
        /** @var Property $property */
        foreach ($properties as $property) {
            $this->appendLine($puml, $this->renderProperty($property));
        }
        if (count($properties) > 0 && count($node->getMethods()) > 0) {
            $this->appendLine($puml);
        }
        return $puml;
    }

    /**
     * @param Property $property
     *
     * @return string
     * @throws RendererException
     */
    private function renderProperty(Property $property): string
    {
        $attributes = $property->getAttributes();
        $propertyType = null;
        if (array_key_exists('comments', $attributes)) {
            /** @var Comment $comment */
            foreach ($attributes['comments'] as $comment) {
                $match = [];
                if (preg_match('~@var\s+([^\s\[\]]+)(\[\])?~', (string) $comment, $match)) {
                    $propertyType = $match[1];
                    if (!$this->renderNamepaceForFlag('p') && strpos($propertyType, '\\') !== false) {
                        $propertyType = substr($propertyType, (strrpos($propertyType, '\\') + 1));
                    }
                    if (isset($match[2])) {
                        $propertyType = 'array<' . $propertyType . '>';
                    }
                }
            }
        }
        $propertyName = null;
        foreach ($property->props as $prop) {
            if (!$prop->name instanceof VarLikeIdentifier) {
                continue;
            }
            $propertyName = (string) $prop->name;
            break;
        }
        $puml = '';
        if ($property->isStatic()) {
            $puml .= '{static} ';
        }
        $puml .= $this->renderVisibility($property) . ($propertyName ?? '') . ':' . ($propertyType ?? 'mixed');
        return $puml;
    }

    /**
     * @param ClassLike $node
     *
     * @return string
     * @throws RendererException
     */
    private function renderMethods(ClassLike $node): string
    {
        $puml = '';
        $methods = $node->getMethods();
        foreach ($methods as $method) {
            $this->appendLine($puml, $this->renderMethod($method));
        }
        return $puml;
    }

    /**
     * Render a method signature
     *
     * @param ClassMethod $method
     *
     * @return string
     * @throws RendererException
     */
    private function renderMethod(ClassMethod $method): string
    {
        $puml = '';
        if ($method->isStatic()) {
            $puml .= '{static} ';
        }
        if ($method->isAbstract()) {
            $puml .= '{abstract} ';
        }
        if ($method->isFinal()) {
            $puml .= '{final} ';
        }
        $puml .= $this->renderVisibility($method);
        $methodName = (string) $method->name;
        $puml .= $methodName . '(';
        /**
         * @var integer $index
         * @var Param $param
         */
        $params = $method->getParams();
        foreach ($params as $index => $param) {
            $paramName = (string) $param->var->name;
            $paramType = $param->type === null
                ? 'mixed'
                : $this->typeRenderer->render($param->type, $this->renderNamepaceForFlag('m'));
            $puml .= $paramName . ':' . $paramType;
            if ($index < (count($params) - 1)) {
                $puml .= ', ';
            }
        }
        $puml .= ')';
        if ($method->getReturnType() !== null) {
            $puml .= ':' . $this->typeRenderer->render($method->getReturnType(), $this->renderNamepaceForFlag('m'));
        }
        return $puml;
    }

    /**
     * Render method/property visibility
     *
     * @param Stmt $node
     *
     * @return string
     * @throws RendererException
     */
    private function renderVisibility(Stmt $node): string
    {
        if (!$node instanceof Property
            && !$node instanceof ClassMethod
        ) {
            throw new RendererException(sprintf('Unable to render visibility for class %s.', get_class($node)));
        }
        if ($node->isPublic()) {
            return '+';
        } elseif ($node->isProtected()) {
            return '#';
        } elseif ($node->isPrivate()) {
            return '-';
        } else {
            throw new RendererException(sprintf('Unable to render visibility for class %s.', get_class($node)));
        }
    }

    /**
     * Render ClassLike inheritance
     *
     * Trait use is treated as multi-inheritance from abstract classes with the <<trait>> stereotype.
     * Get over it.
     *
     * @param ClassLike $node
     *
     * @return string
     * @throws RendererException
     */
    private function renderExtends(ClassLike $node): string
    {
        $puml = '';
        $extends = [];
        if (property_exists($node, 'extends') && !empty($node->extends)) {
            if (is_array($node->extends)) {
                foreach ($node->extends as $extend) {
                    $extends[] = $this->typeRenderer->render($extend, $this->renderNamespace($node));
                }
            } else {
                $extends[] = $this->typeRenderer->render($node->extends, $this->renderNamespace($node));
            }
        }
        if ($node instanceof Class_) {
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof TraitUse) {
                    foreach ($stmt->traits as $trait) {
                        $extends[] = $this->typeRenderer->render($trait, $this->renderNamepaceForFlag('t'));
                    }
                }
            }
        }
        if (count($extends) > 0) {
            $puml .= 'extends ';
            foreach ($extends as $index => $extend) {
                $puml .= $extend;
                if ($index < (count($extends) - 1)) {
                    $puml .= ', ';
                }
            }
            $puml .= ' ';
        }
        return $puml;
    }

    /**
     * @param ClassLike $node
     *
     * @return string
     * @throws RendererException
     */
    private function renderImplements(ClassLike $node): string
    {
        $puml = '';
        if (property_exists($node, 'implements') && !empty($node->implements)) {
            $puml .= 'implements ';
            foreach ($node->implements as $index => $name) {
                $puml .= $this->typeRenderer->render($name, $this->renderNamepaceForFlag('i'));
                if ($index < (count($node->implements) - 1)) {
                    $puml .= ', ';
                }
            }
            $puml .= ' ';
        }
        return $puml;
    }

    /**
     * @param ClassLike $node
     *
     * @return bool
     * @throws RendererException
     */
    private function renderNamespace(ClassLike $node): bool
    {
        return $this->renderNamepaceForFlag($this->getTypeFlag($node));
    }

    /**
     * @param string $flag
     *
     * @return bool
     * @throws RendererException
     */
    private function renderNamepaceForFlag(string $flag): bool
    {
        try {
            return $this->options->hasFlag('namespaced-types', $flag);
        } catch (OptionsException $e) {
            throw new RendererException(
                sprintf('Unable to determine if namespace should be rendered for flag "%s".', $flag),
                1609624689,
                $e
            );
        }
    }

    /**
     * @param ClassLike $node
     *
     * @return string
     * @throws RendererException
     */
    private function getTypeFlag(ClassLike $node): string
    {
        switch (get_class($node)) {
            case Class_::class:
                return 'c';
            case Interface_::class:
                return 'i';
            case Trait_::class:
                return 't';
            default:
                throw new RendererException(sprintf('Unable to determine flag for class "%s".', get_class($node)));
        }
    }
}
