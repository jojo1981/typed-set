<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet\Exception;

use DomainException;
use Exception;
use Jojo1981\PhpTypes\ClassType;
use Jojo1981\PhpTypes\TypeInterface;
use Throwable;
use function sprintf;

/**
 * @package Jojo1981\TypedSet\Exception
 */
class SetException extends DomainException
{
    /**
     * Private constructor, prevent getting an instance of this class using the new keyword from outside the lexical scope of this class.
     *
     * @param string $message
     * @param Throwable|null $previous
     */
    final protected function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return self
     */
    public static function canNotCompareSetsOfDifferenceType(): self
    {
        return new self('Can not compare 2 sets of different types');
    }

    /**
     * @param TypeInterface $expectedType
     * @param TypeInterface $actualType
     * @param null|string $prefixMessage
     * @return self
     */
    public static function dataIsNotOfExpectedType(
        TypeInterface $expectedType,
        TypeInterface $actualType,
        ?string $prefixMessage = null
    ): self {
        return new self(sprintf(
            $prefixMessage . 'Data is not %s: `%s`, but %s: `%s`',
            $expectedType instanceof ClassType ? 'an instance of' : 'of type',
            $expectedType->getName(),
            $actualType instanceof ClassType ? 'an instance of' : 'of type',
            $actualType->getName()
        ));
    }

    /**
     * @param string $type
     * @param null|Exception $previous
     * @return self
     */
    public static function givenTypeIsNotValid(string $type, ?Exception $previous = null): self
    {
        return new self('Given type: `' . $type . '` is not a valid type and also not an existing class', $previous);
    }

    /**
     * @param string $type
     * @param null|Exception $previous
     * @return self
     */
    public static function determinedTypeIsNotValid(string $type, ?Exception $previous = null): self
    {
        return new self('Determined type: `' . $type . '` is not a valid type and also not an existing class', $previous);
    }

    /**
     * @return self
     */
    public static function emptyElementsCanNotDetermineType(): self
    {
        return new self('Elements can not be empty, because type can NOT be determined');
    }

    /**
     * @return self
     */
    public static function typeOmittedOnEmptySet(): self
    {
        return new self('Type can not be omitted on an empty Set');
    }

    /**
     * @param null|Exception $previous
     * @return self
     */
    public static function couldNotCreateTypeFromValue(?Exception $previous = null): self
    {
        return new self('Could not create type from value', $previous);
    }

    /**
     * @param string $typeName
     * @param null|Exception $previous
     * @return self
     */
    public static function couldNotCreateTypeFromTypeName(string $typeName, ?Exception $previous = null): self
    {
        return new self(sprintf('Could not create type from type name: `%s`', $typeName), $previous);
    }

    /**
     * @param string $message
     * @return self
     */
    public static function setIsEmpty(string $message): self
    {
        return new self('Set is empty. ' . $message);
    }
}
