<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet\Handler\Exception;

use Jojo1981\PhpTypes\ClassType;
use Jojo1981\PhpTypes\TypeInterface;
use Jojo1981\TypedSet\Exception\SetException;
use function sprintf;

/**
 * @package Jojo1981\TypedSet\Handler\Exception
 */
final class HandlerException extends SetException
{
    /**
     * @param null|TypeInterface $expectedType
     * @param null|TypeInterface $actualType
     * @return self
     */
    public static function canNotHandleElement(?TypeInterface $expectedType = null, ?TypeInterface $actualType = null): self
    {
        if (null === $expectedType) {
            return new self('Can not handle element');
        }
        if (null === $actualType) {
            return new self(sprintf(
                'Can not handle element, element not %s: `%s`',
                $expectedType instanceof ClassType ? 'an instance of' : 'of type',
                $expectedType->getName()
            ));
        }

        return new self(sprintf(
            'Can not handle element, element not %s: `%s`, but %s: `%s`',
            $expectedType instanceof ClassType ? 'an instance of' : 'of type',
            $expectedType->getName(),
            $actualType instanceof ClassType ? 'an instance of' : 'of type',
            $actualType->getName()
        ));
    }

    /**
     * @return self
     */
    public static function canNotHandleElementBecauseNoHandlerAvailable(): self
    {
        return new self('Can not handle the element, because there is no handler registered which support this element');
    }
}
