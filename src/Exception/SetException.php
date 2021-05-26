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

use Jojo1981\PhpTypes\ClassType;
use Jojo1981\PhpTypes\TypeInterface;

/**
 * @package Jojo1981\TypedSet\Exception
 */
class SetException extends \DomainException
{
    /**
     * @return SetException
     */
    public static function canNotCompareSetsOfDifferenceType(): SetException
    {
        return new static('Can not compare 2 sets of different types');
    }

    /**
     * @param TypeInterface $expectedType
     * @param TypeInterface $actualType
     * @param null|string $prefixMessage
     * @return SetException
     */
    public static function dataIsNotOfExpectedType(
        TypeInterface $expectedType,
        TypeInterface $actualType,
        ?string $prefixMessage = null
    ): SetException
    {
        return new self(\sprintf(
            $prefixMessage . 'Data is not %s: `%s`, but %s: `%s`',
            $expectedType instanceof ClassType ? 'an instance of' : 'of type',
            $expectedType->getName(),
            $actualType instanceof ClassType ? 'an instance of' : 'of type',
            $actualType->getName()
        ));
    }

    /**
     * @param string $type
     * @param null|\Exception $previous
     * @return SetException
     */
    public static function givenTypeIsNotValid(string $type, ?\Exception $previous = null): SetException
    {
        return new self(
            'Given type: `' . $type . '` is not a valid type and also not an existing class',
            0,
            $previous
        );
    }

    /**
     * @param string $type
     * @param null|\Exception $previous
     * @return SetException
     */
    public static function determinedTypeIsNotValid(string $type, ?\Exception $previous = null): SetException
    {
        return new self(
            'Determined type: `' . $type . '` is not a valid type and also not an existing class',
            0,
            $previous
        );
    }

    /**
     * @return SetException
     */
    public static function emptyElementsCanNotDetermineType(): SetException
    {
        return new self('Elements can not be empty, because type can NOT be determined');
    }

    /**
     * @return SetException
     */
    public static function typeOmittedOnEmptySet(): SetException
    {
        return new self('Type can not be omitted on an empty Set');
    }

    /**
     * @param null|\Exception $previous
     * @return SetException
     */
    public static function couldNotCreateTypeFromValue(?\Exception $previous = null): SetException
    {
        return new self('Could not create type from value', 0, $previous);
    }

    /**
     * @param string $typeName
     * @param null|\Exception $previous
     * @return SetException
     */
    public static function couldNotCreateTypeFromTypeName(string $typeName, ?\Exception $previous = null): SetException
    {
        return new self(
            \sprintf('Could not create type from type name: `%s`', $typeName),
            0,
            $previous
        );
    }

    /**
     * @param string $message
     * @return SetException
     */
    public static function setIsEmpty(string $message): SetException
    {
        return new self('Set is empty. ' . $message);
    }
}
