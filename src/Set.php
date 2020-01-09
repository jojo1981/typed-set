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

use Jojo1981\PhpTypes\AbstractType;
use Jojo1981\PhpTypes\ClassType;
use Jojo1981\PhpTypes\Exception\TypeException;
use Jojo1981\PhpTypes\TypeInterface;
use Jojo1981\TypedSet\Exception\SetException;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;
use Jojo1981\TypedSet\Handler\GlobalHandler;

/**
 * A mutable unordered type safe set.
 *
 * @package Jojo1981\TypedSet
 */
class Set implements \Countable, \IteratorAggregate
{
    /** @var string[] */
    private const NOT_SUPPORTED_TYPES = ['mixed', 'null', 'void', 'bool', 'boolean', 'resource'];

    /** @var TypeInterface */
    private $type;

    /** @var array */
    private $elements = [];

    /**
     * @param string $type
     * @param array $elements
     * @throws SetException
     */
    public function __construct(string $type, array $elements = [])
    {
        static::assertType($type);
        $this->type = self::createTypeFromName($type);
        $this->addAll($elements);
    }

    /**
     * @param mixed $element
     * @throws SetException
     * @throws HandlerException
     * @return void
     */
    public function add($element): void
    {
        $this->assertElementIsValid($element);
        if (!$this->contains($element)) {
            $this->elements[$this->getHashForElement($element)] = $element;
        }
    }

    /**
     * @param array $elements
     * @throws SetException
     * @throws HandlerException
     * @return void
     */
    public function addAll(array $elements = []): void
    {
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    /**
     * @param mixed $element
     * @throws SetException
     * @throws HandlerException
     * @return bool
     */
    public function contains($element): bool
    {
        $this->assertElementIsValid($element);

        return \array_key_exists($this->getHashForElement($element), $this->elements);
    }

    /**
     * @param mixed $element
     * @throws SetException
     * @return void
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
     * @return array
     */
    public function toArray(): array
    {
        return \array_values($this->elements);
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
     * @throws SetException
     * @throws HandlerException
     * @return bool
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
     * @throws SetException
     * @return DifferenceResult
     */
    public function compare(Set $other): DifferenceResult
    {
        if (!$this->isEqualType($other->type)) {
            throw new SetException('Can not compare 2 sets of different types');
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
     * @return int
     */
    public function count(): int
    {
        return \count($this->elements);
    }

    /**
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->toArray());
    }

    /**
     * @param mixed $element
     * @throws SetException
     * @return void
     */
    private function assertElementIsValid($element): void
    {
        if (!$this->type->isAssignableValue($element)) {
            $otherType = self::createTypeFromValue($element);
            throw new SetException(\sprintf(
                'Data is not %s: `%s`, but %s: `%s`',
                $this->type instanceof ClassType ? 'an instance of' : 'of type',
                $this->type->getName(),
                $otherType instanceof ClassType ? 'an instance of' : 'of type',
                $otherType->getName()
            ));
        }
    }

    /**
     * @param mixed $element
     * @throws HandlerException
     * @return string
     */
    private function getHashForElement($element): string
    {
        if (\is_object($element)) {
            if ($element instanceof HashableInterface) {
                return $element->getHash();
            }

            if (GlobalHandler::getInstance()->support($element, $this->type)) {
                return GlobalHandler::getInstance()->getHash($element, $this->type);
            }

            return \spl_object_hash($element);
        }

        if (GlobalHandler::getInstance()->support($element, $this->type)) {
            return GlobalHandler::getInstance()->getHash($element, $this->type);
        }

        if (\is_array($element)) {
            return \hash('sha256', \json_encode($element));
        }

        return (string) $element;
    }

    /**
     * @param array $elements
     * @throws SetException
     * @return Set
     */
    public static function createFromElements(array $elements): Set
    {
        if (empty($elements)) {
            throw SetException::emptyElementsCanNotDetermineType();
        }

        return new self((self::createTypeFromValue(\reset($elements)))->getName(), $elements);
    }

    /**
     * @param string $type
     * @throws SetException
     * @return void
     */
    private static function assertType(string $type): void
    {
        if (\in_array(\strtolower($type), self::NOT_SUPPORTED_TYPES)) {
            throw SetException::typeIsNotValid($type);
        }

        self::createTypeFromName($type);
    }

    /**
     * @param mixed $value
     * @throws SetException
     * @return TypeInterface
     */
    private static function createTypeFromValue($value): TypeInterface
    {
        try {
            return AbstractType::createFromValue($value);
        } catch (TypeException $exception) { // @codeCoverageIgnore
            throw SetException::couldNotCreateTypeFromValue($exception); // @codeCoverageIgnore
        }
    }

    /**
     * @param string $name
     * @throws SetException
     * @return TypeInterface
     */
    private static function createTypeFromName(string $name): TypeInterface
    {
        try {
            return AbstractType::createFromTypeName($name);
        } catch (TypeException $exception) {
            throw SetException::typeIsNotValid($name, $exception);
        }
    }
}