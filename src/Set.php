<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet;

use ArrayIterator;
use Countable;
use Exception;
use IteratorAggregate;
use Jojo1981\Contracts\HashableInterface as ContractsHashableInterface;
use Jojo1981\PhpTypes\AbstractType;
use Jojo1981\PhpTypes\ClassType;
use Jojo1981\PhpTypes\Exception\TypeException;
use Jojo1981\PhpTypes\TypeInterface;
use Jojo1981\TypedSet\Exception\SetException;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;
use Jojo1981\TypedSet\Handler\GlobalHandler;
use Traversable;
use function array_filter;
use function array_key_exists;
use function array_key_first;
use function array_pop;
use function array_shift;
use function array_values;
use function count;
use function hash;
use function in_array;
use function is_array;
use function is_object;
use function json_encode;
use function reset;
use function spl_object_hash;
use function strtolower;

/**
 * A mutable unordered type safe set.
 *
 * @package Jojo1981\TypedSet
 * @template T
 */
class Set implements Countable, IteratorAggregate
{
    /** @var string[] */
    private const NOT_SUPPORTED_TYPES = ['mixed', 'null', 'void', 'bool', 'boolean', 'resource'];

    /** @var TypeInterface */
    private TypeInterface $type;

    /** @var T[] */
    private array $elements = [];

    /**
     * @param string $type
     * @param Set<T>|T[] $elements
     * @throws HandlerException
     * @throws SetException
     */
    public function __construct(string $type, Set|array $elements = [])
    {
        static::assertGivenType($type);
        $this->type = self::createTypeFromName($type);
        $this->addAll($elements instanceof Set ? $elements->toArray() : $elements);
    }

    /**
     * @param T $element
     * @return void
     * @throws SetException
     * @throws HandlerException
     */
    public function add($element): void
    {
        $this->assertElementIsValid($element);
        if (!$this->contains($element)) {
            $this->elements[$this->getHashForElement($element)] = $element;
        }
    }

    /**
     * @param T[] $elements
     * @return void
     * @throws SetException
     * @throws HandlerException
     */
    public function addAll(array $elements = []): void
    {
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    /**
     * @param T $element
     * @return bool
     * @throws SetException
     * @throws HandlerException
     */
    public function contains($element): bool
    {
        $this->assertElementIsValid($element);

        return array_key_exists($this->getHashForElement($element), $this->elements);
    }

    /**
     * @param T $element
     * @return void
     * @throws SetException
     * @throws HandlerException
     */
    public function remove($element): void
    {
        $this->assertElementIsValid($element);
        if ($this->contains($element)) {
            unset($this->elements[$this->getHashForElement($element)]);
        }
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->elements = [];
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * @return bool
     */
    public function isNonEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * @return T[]
     */
    public function toArray(): array
    {
        return array_values($this->elements);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type->getName();
    }

    /**
     * @param TypeInterface $type
     * @return bool
     */
    public function isEqualType(TypeInterface $type): bool
    {
        return $this->type->isEqual($type);
    }

    /**
     * @param Set $other
     * @return bool
     * @throws SetException
     * @throws HandlerException
     */
    public function isEqual(Set $other): bool
    {
        if (!$this->isEqualType($other->type) || $this->count() !== $other->count()) {
            return false;
        }
        foreach ($this->elements as $element) {
            if (!$other->contains($element)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Set $other
     * @return DifferenceResult
     * @throws SetException
     * @throws HandlerException
     */
    public function compare(Set $other): DifferenceResult
    {
        if (!$this->isEqualType($other->type)) {
            throw SetException::canNotCompareSetsOfDifferenceType();
        }

        $lhsElementsMissing = [];
        $lhsElementsExtra = [];
        $rhsElementsMissing = [];
        $rhsElementsExtra = [];
        $same = [];
        $equal = true;

        foreach ($this->elements as $ownElement) {
            if (!$other->contains($ownElement)) {
                $equal = false;
                $lhsElementsExtra[] = $ownElement;
                $rhsElementsMissing[] = $ownElement;
            } else {
                $same[] = $ownElement;
            }
        }
        foreach ($other->toArray() as $otherElement) {
            if (!$this->contains($otherElement)) {
                $equal = false;
                $rhsElementsExtra[] = $otherElement;
                $lhsElementsMissing[] = $otherElement;
            }
        }

        return new DifferenceResult(
            new Difference($lhsElementsMissing, $lhsElementsExtra),
            new Difference($rhsElementsMissing, $rhsElementsExtra),
            $same,
            $equal
        );
    }

    /**
     * Map this Set into new Set using the passed mapper callback. When type is omitted it will be determined by the
     * first result the mapper function produces.
     *
     * @param callable $mapper
     * @param null|string $type
     * @return Set
     * @throws SetException
     * @throws HandlerException
     */
    public function map(callable $mapper, ?string $type = null): Set
    {
        if (null === $type && $this->isEmpty()) {
            throw SetException::typeOmittedOnEmptySet();
        }

        if (null !== $type) {
            static::assertGivenType($type);
            $typeObject = self::createTypeFromName($type);
        } else {
            $typeObject = self::createTypeFromValue($mapper(reset($this->elements), 0, array_key_first($this->elements)));
            self::assertDeterminedType($typeObject->getName());
        }

        $newElements = [];
        $position = 0;
        foreach ($this->elements as $id => $element) {
            $newElement = $mapper($element, $position++, $id);
            if (!$typeObject->isAssignableValue($newElement)) {
                throw SetException::dataIsNotOfExpectedType(
                    $typeObject,
                    self::createTypeFromValue($newElement),
                    'Mapper is not returning a correct value. '
                );
            }
            $newElements[] = $newElement;
        }

        return new Set($typeObject->getName(), $newElements);
    }

    /**
     * @return T
     * @throws SetException
     */
    public function popElement()
    {
        if ($this->isEmpty()) {
            throw SetException::setIsEmpty('Could not pop an element of the end of of the set.');
        }

        return array_pop($this->elements);
    }

    /**
     * @return T
     * @throws SetException
     */
    public function shiftElement()
    {
        if ($this->isEmpty()) {
            throw SetException::setIsEmpty('Could not shift an element of the beginning of the set.');
        }

        return array_shift($this->elements);
    }

    /**
     * @param callable $predicate
     * @return Set
     * @throws SetException
     * @throws HandlerException
     */
    public function filter(callable $predicate): Set
    {
        return new Set($this->type->getName(), array_filter($this->elements, $predicate));
    }

    /**
     * @param callable $predicate
     * @return T|null
     */
    public function find(callable $predicate)
    {
        foreach ($this->elements as $index => $element) {
            if (true === $predicate($element, $index)) {
                return $element;
            }
        }

        return null;
    }

    /**
     * @param callable $predicate
     * @return bool
     */
    public function all(callable $predicate): bool
    {
        foreach ($this->elements as $index => $element) {
            if (false === $predicate($element, $index)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param callable $predicate
     * @return bool
     */
    public function some(callable $predicate): bool
    {
        foreach ($this->elements as $index => $element) {
            if (true === $predicate($element, $index)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param callable $predicate
     * @return bool
     */
    public function none(callable $predicate): bool
    {
        foreach ($this->elements as $index => $element) {
            if (true === $predicate($element, $index)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * Merge other set(s) into this set. All sets should be exactly of the same type.
     *
     * @param Set $otherSet
     * @param Set ...$otherSets
     * @return $this
     * @throws SetException
     * @throws HandlerException
     */
    public function merge(Set $otherSet, Set ...$otherSets): self
    {
        array_unshift($otherSets, $otherSet);

        foreach ($otherSets as $currentOtherCollection) {
            if (!$this->type->isAssignableType($currentOtherCollection->type)) {
                throw SetException::couldNotMergeSets($this->type->getName(), $otherSet->getType());
            }

            $this->addAll($currentOtherCollection->toArray());
        }

        return $this;
    }

    /**
     * @param T $element
     * @return void
     * @throws SetException
     */
    private function assertElementIsValid($element): void
    {
        if (!$this->type->isAssignableValue($element)) {
            $otherType = self::createTypeFromValue($element);
            throw SetException::dataIsNotOfExpectedType($this->type, $otherType);
        }
    }

    /**
     * @param T $element
     * @throws HandlerException
     * @return string
     */
    private function getHashForElement($element): string
    {
        if (is_object($element)) {
            if ($this->type instanceof ClassType) {
                if ($element instanceof ContractsHashableInterface) {
                    return $element->getHash();
                }

                if (GlobalHandler::getInstance()->support($element, $this->type)) {
                    return GlobalHandler::getInstance()->getHash($element, $this->type);
                }
            }

            return spl_object_hash($element);
        }

        if (GlobalHandler::getInstance()->support($element, $this->type)) {
            return GlobalHandler::getInstance()->getHash($element, $this->type);
        }

        if (is_array($element)) {
            return hash('sha256', json_encode($element));
        }

        return (string) $element;
    }

    /**
     * @param T[] $elements
     * @return Set
     * @throws SetException
     * @throws HandlerException
     */
    public static function createFromElements(array $elements): Set
    {
        if (empty($elements)) {
            throw SetException::emptyElementsCanNotDetermineType();
        }

        return new self((self::createTypeFromValue(reset($elements)))->getName(), $elements);
    }

    /**
     * Create one collection from multiple collections.
     *
     * @param string $type
     * @param Set[] $sets
     * @return Set
     * @throws SetException
     * @throws HandlerException
     */
    public static function createFromSets(string $type, array $sets): Set
    {
        self::assertGivenType($type);
        $typeValue = self::createTypeFromName($type);
        self::assertSets($sets, $typeValue);

        $result = new Set($typeValue->getName());
        foreach ($sets as $set) {
            $result->merge($set);
        }

        return $result;
    }

    /**
     * @param Set[] $sets
     * @param TypeInterface $typeValue
     * @return void
     * @throws SetException
     */
    private static function assertSets(array $sets, TypeInterface $typeValue): void
    {
        if (empty($sets)) {
            throw SetException::emptySets();
        }
        if (count($sets) < 2) {
            throw SetException::notEnoughSets();
        }
        foreach ($sets as $collection) {
            if (!$collection instanceof self) {
                throw SetException::invalidSetsData();
            }
            if (!$typeValue->isAssignableType($collection->type)) {
                throw SetException::setsNotAllOfSameType($typeValue, $collection->type);
            }
        }
    }

    /**
     * @param string $type
     * @throws SetException
     * @return void
     */
    private static function assertGivenType(string $type): void
    {
        if (in_array(strtolower($type), self::NOT_SUPPORTED_TYPES)) {
            throw SetException::givenTypeIsNotValid($type);
        }

        try {
            self::createTypeFromName($type);
        } catch (Exception $exception) {
            throw SetException::givenTypeIsNotValid($type, $exception);
        }
    }

    /**
     * @param string $type
     * @throws SetException
     * @return void
     */
    private static function assertDeterminedType(string $type): void
    {
        if (in_array(strtolower($type), self::NOT_SUPPORTED_TYPES)) {
            throw SetException::determinedTypeIsNotValid($type);
        }

        try {
            self::createTypeFromName($type);
        } catch (Exception $exception) { // @codeCoverageIgnore
            throw SetException::determinedTypeIsNotValid($type, $exception); // @codeCoverageIgnore
        }
    }

    /**
     * @param mixed $value
     * @return TypeInterface
     * @throws SetException
     */
    private static function createTypeFromValue(mixed $value): TypeInterface
    {
        try {
            return AbstractType::createFromValue($value);
        } catch (TypeException $exception) { // @codeCoverageIgnore
            throw SetException::couldNotCreateTypeFromValue($exception); // @codeCoverageIgnore
        }
    }

    /**
     * @param string $typeName
     * @return TypeInterface
     * @throws SetException
     */
    private static function createTypeFromName(string $typeName): TypeInterface
    {
        try {
            return AbstractType::createFromTypeName($typeName);
        } catch (TypeException $exception) {
            throw SetException::couldNotCreateTypeFromTypeName($typeName, $exception);
        }
    }
}
