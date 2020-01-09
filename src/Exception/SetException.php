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

/**
 * @package Jojo1981\TypedSet\Exception
 */
class SetException extends \DomainException
{
    /**
     * @param string $type
     * @param null|\Exception $previous
     * @return SetException
     */
    public static function typeIsNotValid(string $type, ?\Exception $previous = null): SetException
    {
        return new self(
            'Given type: `' . $type . '` is not a valid primitive type and also not an existing class',
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
     * @param \Exception $previous
     * @return SetException
     */
    public static function couldNotCreateTypeFromValue(\Exception $previous): SetException
    {
        return new self('Could not create type from value', 0, $previous);
    }
}