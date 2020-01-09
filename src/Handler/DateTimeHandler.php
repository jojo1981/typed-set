<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet\Handler;

use Jojo1981\PhpTypes\AbstractType;
use Jojo1981\PhpTypes\Exception\TypeException;
use Jojo1981\PhpTypes\TypeInterface;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;
use Jojo1981\TypedSet\HandlerInterface;

/**
 * @package Jojo1981\TypedSet\Handler
 */
final class DateTimeHandler implements HandlerInterface
{
    /**
     * @param mixed $element
     * @param TypeInterface $type
     * @return bool
     */
    public function support($element, TypeInterface $type): bool
    {
        return $element instanceof \DateTimeInterface;
    }

    /**
     * @param \DateTimeInterface $element
     * @param TypeInterface $type
     * @throws HandlerException
     * @return string
     */
    public function getHash($element, TypeInterface $type): string
    {
        if (!$element instanceof \DateTimeInterface) {
            $expectedType = null;
            $actualType = null;
            try {
                $expectedType = AbstractType::createFromTypeName(\DateTimeInterface::class);
                $actualType = AbstractType::createFromValue($element);
            } catch (TypeException $exception) {} // @codeCoverageIgnore

            throw HandlerException::canNotHandleElement($expectedType, $actualType);
        }

        return \hash('sha256', (string) $element->getTimestamp());
    }
}