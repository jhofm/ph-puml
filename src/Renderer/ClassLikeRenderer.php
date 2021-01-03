<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Renderer;

use Jhofm\PhPuml\Options\Options;
use Jhofm\PhPuml\Options\OptionsException;
use PhpParser\Comment;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
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
     */
    public function __construct(TypeRenderer $typeRenderer)
    {
        $this->typeRenderer = $typeRenderer;
    }

    /**
     * Render a ClassLike Node as PlantUML
     *
     * @param ClassLike $node
     * @param Options $options
     *
     * @return string
     * @throws RendererException
     */
    public function render(ClassLike $node, Options $options): string
    {
        $this->options = $options;
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
        $className = $this->typeRenderer->render($node, $this->renderNamepaceForFlag('c'));
        $puml .= $className . ' ';
        if ($node instanceof Class_ && $node->isFinal()) {
            $puml .= '<<leaf>> ';
        }
        if ($node instanceof Trait_) {
            $puml .= '<<trait>> ';
        }
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
            $this->appendLine($puml, $this->renderMethod($method, $node));
        }
        return $puml;
    }

    /**
     * Render a method signature
     *
     * @param ClassMethod $method
     * @param ClassLike $classLike
     *
     * @return string
     * @throws RendererException
     */
    private function renderMethod(ClassMethod $method, ClassLike $classLike): string
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
        if ((string) $method->name === '__construct') {
            $puml .= '<<create>> ' . $this->typeRenderer->render($classLike, false) . ' ';
        } elseif ($method->isStatic()
            && ($method->getReturnType() instanceof Name || $method->getReturnType() instanceof Identifier)
            && $method->getReturnType()->isSpecialClassName()
            && in_array((string) $method->getReturnType(), ['self', 'static'])
        ) {
            $puml .= '<<create>> ' . (string) $method->name . ' ';
        } else {
            $puml .= (string) $method->name . ' ';
        }
        $puml .= '(';
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
}
