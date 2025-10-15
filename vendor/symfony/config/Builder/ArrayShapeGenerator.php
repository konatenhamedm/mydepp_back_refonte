<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Config\Builder;

use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\BaseNode;
use Symfony\Component\Config\Definition\BooleanNode;
use Symfony\Component\Config\Definition\EnumNode;
use Symfony\Component\Config\Definition\FloatNode;
use Symfony\Component\Config\Definition\IntegerNode;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\NumericNode;
use Symfony\Component\Config\Definition\PrototypedArrayNode;
use Symfony\Component\Config\Definition\ScalarNode;
use Symfony\Component\Config\Definition\StringNode;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @internal
 */
final class ArrayShapeGenerator
{
    public static function generate(NodeInterface $node): string
    {
        return str_replace("\n", "\n * ", self::doGeneratePhpDoc($node));
    }

    private static function doGeneratePhpDoc(NodeInterface $node, int $nestingLevel = 1): string
    {
        if (!$node instanceof ArrayNode) {
            return match (true) {
                $node instanceof BooleanNode => $node->hasDefaultValue() && null === $node->getDefaultValue() ? 'bool|null' : 'bool',
                $node instanceof StringNode => 'string',
                $node instanceof NumericNode => self::handleNumericNode($node),
                $node instanceof EnumNode => $node->getPermissibleValues('|'),
                $node instanceof ScalarNode => 'scalar|null',
                default => 'mixed',
            };
        }

        if ($node instanceof PrototypedArrayNode) {
            $isHashmap = (bool) $node->getKeyAttribute();
            $arrayType = ($isHashmap ? 'array<string, ' : 'list<').self::doGeneratePhpDoc($node->getPrototype(), 1 + $nestingLevel).'>';

            return $node->hasDefaultValue() && null === $node->getDefaultValue() ? $arrayType.'|null' : $arrayType;
        }

        if (!($children = $node->getChildren()) && !$node->getParent() instanceof PrototypedArrayNode) {
            return $node->hasDefaultValue() && null === $node->getDefaultValue() ? 'array<mixed>|null' : 'array<mixed>';
        }

        $arrayShape = \sprintf("array{%s\n", self::generateInlinePhpDocForNode($node));

        foreach ($children as $child) {
            $arrayShape .= str_repeat('    ', $nestingLevel).self::dumpNodeKey($child).': ';

            if ($child instanceof PrototypedArrayNode) {
                $isHashmap = (bool) $child->getKeyAttribute();
                $childArrayType = ($isHashmap ? 'array<string, ' : 'list<').self::doGeneratePhpDoc($child->getPrototype(), 1 + $nestingLevel).'>';
                $arrayShape .= $child->hasDefaultValue() && null === $child->getDefaultValue() ? $childArrayType.'|null' : $childArrayType;
            } else {
                $arrayShape .= self::doGeneratePhpDoc($child, 1 + $nestingLevel);
            }

            $arrayShape .= \sprintf(",%s\n", !$child instanceof ArrayNode ? self::generateInlinePhpDocForNode($child) : '');
        }

        if ($node->shouldIgnoreExtraKeys()) {
            $arrayShape .= str_repeat('    ', $nestingLevel)."...<mixed>\n";
        }

        $arrayShape = $arrayShape.str_repeat('    ', $nestingLevel - 1).'}';

        return $node->hasDefaultValue() && null === $node->getDefaultValue() ? $arrayShape.'|null' : $arrayShape;
    }

    private static function dumpNodeKey(NodeInterface $node): string
    {
        $name = $node->getName();
        $quoted = str_starts_with($name, '@')
            || \in_array(strtolower($name), ['int', 'float', 'bool', 'null', 'scalar'], true)
            || strpbrk($name, '\'"');

        if ($quoted) {
            $name = "'".addslashes($name)."'";
        }

        return $name.($node->isRequired() ? '' : '?');
    }

    private static function handleNumericNode(NumericNode $node): string
    {
        $min = $node->getMin() ?? 'min';
        $max = $node->getMax() ?? 'max';

        if ($node instanceof IntegerNode) {
            return \sprintf('int<%s, %s>', $min, $max);
        }
        if ($node instanceof FloatNode) {
            return 'float';
        }

        return \sprintf('int<%s, %s>|float', $min, $max);
    }

    private static function generateInlinePhpDocForNode(BaseNode $node): string
    {
        $comment = '';
        if ($node->isDeprecated()) {
            $comment .= ' // Deprecated: '.$node->getDeprecation($node->getName(), $node->getPath())['message'];
        }

        if ($info = $node->getInfo()) {
            $comment .= ' // '.$info;
        }

        if ($node->hasDefaultValue()) {
            $comment .= ' // Default: '.json_encode($node->getDefaultValue(), \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_PRESERVE_ZERO_FRACTION);
        }

        return rtrim(preg_replace('/\s+/', ' ', $comment));
    }
}
