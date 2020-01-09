<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet\TestSuite\Fixture;

use Jojo1981\PhpTypes\TypeInterface;
use Jojo1981\TypedSet\HandlerInterface;

/**
 * @package Jojo1981\TypedSet\TestSuite\Fixture
 */
class PersonHandler implements HandlerInterface
{
    /**
     * @param mixed $element
     * @param TypeInterface $type
     * @return bool
     */
    public function support($element, TypeInterface $type): bool
    {
        return $element instanceof Person;
    }

    /**
     * @param Person $element
     * @param TypeInterface $type
     * @return string
     */
    public function getHash($element, TypeInterface $type): string
    {
        return $element->getName() . '|' . $element->getAge();
    }
}