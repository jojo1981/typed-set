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

use Jojo1981\PhpTypes\TypeInterface;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;

/**
 * @package Jojo1981\TypedSet
 */
interface HandlerInterface
{
    /**
     * Return true when the handler support the given element.
     * Only when returning true the getHash method will be called.
     *
     * @param mixed $element
     * @param TypeInterface $type
     * @return bool
     */
    public function support($element, TypeInterface $type): bool;

    /**
     * Return a hash string for the given element, but throw a HandlerException when something is wrong.
     *
     * @param mixed $element
     * @param TypeInterface $type
     * @throws HandlerException
     * @return string
     */
    public function getHash($element, TypeInterface $type): string;
}