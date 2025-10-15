<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Exception\MappingException;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
final class AttributeMetadataPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('validator.builder')) {
            return;
        }

        $resolve = $container->getParameterBag()->resolveValue(...);
        $mappedClasses = [];
        foreach ($container->getDefinitions() as $id => $definition) {
            if (!$definition->hasTag('validator.attribute_metadata')) {
                continue;
            }
            if (!$definition->hasTag('container.excluded')) {
                throw new InvalidArgumentException(\sprintf('The resource "%s" tagged "validator.attribute_metadata" is missing the "container.excluded" tag.', $id));
            }
            $class = $resolve($definition->getClass());
            foreach ($definition->getTag('validator.attribute_metadata') as $attributes) {
                if ($class !== $for = $attributes['for'] ?? $class) {
                    $this->checkSourceMapsToTarget($container, $class, $for);
                }

                $mappedClasses[$for][$class] = true;
            }
        }

        if (!$mappedClasses) {
            return;
        }

        ksort($mappedClasses);

        $container->getDefinition('validator.builder')
            ->addMethodCall('addAttributeMappings', [array_map('array_keys', $mappedClasses)]);
    }

    private function checkSourceMapsToTarget(ContainerBuilder $container, string $source, string $target): void
    {
        $source = $container->getReflectionClass($source);
        $target = $container->getReflectionClass($target);

        foreach ($source->getProperties() as $p) {
            if ($p->class === $source->name && !($target->hasProperty($p->name) && $target->getProperty($p->name)->class === $target->name)) {
                throw new MappingException(\sprintf('The property "%s" on "%s" is not present on "%s".', $p->name, $source->name, $target->name));
            }
        }

        foreach ($source->getMethods() as $m) {
            if ($m->class === $source->name && !($target->hasMethod($m->name) && $target->getMethod($m->name)->class === $target->name)) {
                throw new MappingException(\sprintf('The method "%s" on "%s" is not present on "%s".', $m->name, $source->name, $target->name));
            }
        }
    }
}
