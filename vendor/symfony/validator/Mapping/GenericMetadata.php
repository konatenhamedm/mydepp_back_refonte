<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Mapping;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Cascade;
use Symfony\Component\Validator\Constraints\DisableAutoMapping;
use Symfony\Component\Validator\Constraints\EnableAutoMapping;
use Symfony\Component\Validator\Constraints\Traverse;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * A generic container of {@link Constraint} objects.
 *
 * This class supports serialization and cloning.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class GenericMetadata implements MetadataInterface
{
    /**
     * @var Constraint[]
     */
    private array $constraints = [];

    /**
     * @var array<string, Constraint[]>
     */
    private array $constraintsByGroup = [];

    /**
     * The strategy for cascading objects.
     *
     * By default, objects are not cascaded.
     *
     * @var CascadingStrategy::*
     */
    private int $cascadingStrategy = CascadingStrategy::NONE;

    /**
     * The strategy for traversing traversable objects.
     *
     * By default, traversable objects are not traversed.
     *
     * @var TraversalStrategy::*
     */
    private int $traversalStrategy = TraversalStrategy::NONE;

    /**
     * Is auto-mapping enabled?
     *
     * @var AutoMappingStrategy::*
     */
    private int $autoMappingStrategy = AutoMappingStrategy::NONE;

    public function __serialize(): array
    {
        if (self::class === (new \ReflectionMethod($this, '__sleep'))->class || self::class !== (new \ReflectionMethod($this, '__serialize'))->class) {
            return array_filter([
                'constraints' => $this->constraints,
                'constraintsByGroup' => $this->constraintsByGroup,
                'cascadingStrategy' => CascadingStrategy::NONE !== $this->cascadingStrategy ? $this->cascadingStrategy : null,
                'traversalStrategy' => TraversalStrategy::NONE !== $this->traversalStrategy ? $this->traversalStrategy : null,
                'autoMappingStrategy' => AutoMappingStrategy::NONE !== $this->autoMappingStrategy ? $this->autoMappingStrategy : null,
            ]);
        }

        trigger_deprecation('symfony/validator', '7.4', 'Implementing "%s::__sleep()" is deprecated, use "__serialize()" instead.', get_debug_type($this));

        $data = [];
        foreach ($this->__sleep() as $key) {
            try {
                if (($r = new \ReflectionProperty($this, $key))->isInitialized($this)) {
                    $data[$key] = $r->getValue($this);
                }
            } catch (\ReflectionException) {
                $data[$key] = $this->$key;
            }
        }

        return $data;
    }

    /**
     * @deprecated since Symfony 7.4, will be replaced by `__serialize()` in 8.0
     */
    public function __sleep(): array
    {
        trigger_deprecation('symfony/validator', '7.4', 'Calling "%s::__sleep()" is deprecated, use "__serialize()" instead.', get_debug_type($this));

        return [
            'constraints',
            'constraintsByGroup',
            'cascadingStrategy',
            'traversalStrategy',
            'autoMappingStrategy',
        ];
    }

    public function __clone()
    {
        $constraints = $this->constraints;

        $this->constraints = [];
        $this->constraintsByGroup = [];

        foreach ($constraints as $constraint) {
            $this->addConstraint(clone $constraint);
        }
    }

    /**
     * Adds a constraint.
     *
     * If the constraint {@link Valid} is added, the cascading strategy will be
     * changed to {@link CascadingStrategy::CASCADE}. Depending on the
     * $traverse property of that constraint, the traversal strategy
     * will be set to one of the following:
     *
     *  - {@link TraversalStrategy::IMPLICIT} if $traverse is enabled
     *  - {@link TraversalStrategy::NONE} if $traverse is disabled
     *
     * @return $this
     *
     * @throws ConstraintDefinitionException When trying to add the {@link Cascade}
     *                                       or {@link Traverse} constraint
     */
    public function addConstraint(Constraint $constraint): static
    {
        if ($constraint instanceof Traverse || $constraint instanceof Cascade) {
            throw new ConstraintDefinitionException(\sprintf('The constraint "%s" can only be put on classes. Please use "Symfony\Component\Validator\Constraints\Valid" instead.', get_debug_type($constraint)));
        }

        if ($constraint instanceof Valid && null === $constraint->groups) {
            $this->cascadingStrategy = CascadingStrategy::CASCADE;
            $this->traversalStrategy = $constraint->traverse ? TraversalStrategy::IMPLICIT : TraversalStrategy::NONE;

            // The constraint is not added
            return $this;
        }

        if ($constraint instanceof DisableAutoMapping || $constraint instanceof EnableAutoMapping) {
            $this->autoMappingStrategy = $constraint instanceof EnableAutoMapping ? AutoMappingStrategy::ENABLED : AutoMappingStrategy::DISABLED;

            // The constraint is not added
            return $this;
        }

        if (!\in_array($constraint, $this->constraints, true)) {
            $this->constraints[] = $constraint;
        }

        foreach ($constraint->groups as $group) {
            if (!\in_array($constraint, $this->constraintsByGroup[$group] ??= [], true)) {
                $this->constraintsByGroup[$group][] = $constraint;
            }
        }

        return $this;
    }

    /**
     * Adds a list of constraints.
     *
     * @param Constraint[] $constraints
     *
     * @return $this
     */
    public function addConstraints(array $constraints): static
    {
        foreach ($constraints as $constraint) {
            $this->addConstraint($constraint);
        }

        return $this;
    }

    /**
     * @return Constraint[]
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * Returns whether this element has any constraints.
     */
    public function hasConstraints(): bool
    {
        return \count($this->constraints) > 0;
    }

    /**
     * Aware of the global group (* group).
     *
     * @return Constraint[]
     */
    public function findConstraints(string $group): array
    {
        return $this->constraintsByGroup[$group] ?? [];
    }

    public function getCascadingStrategy(): int
    {
        return $this->cascadingStrategy;
    }

    public function getTraversalStrategy(): int
    {
        return $this->traversalStrategy;
    }

    /**
     * @see AutoMappingStrategy
     */
    public function getAutoMappingStrategy(): int
    {
        return $this->autoMappingStrategy;
    }
}
