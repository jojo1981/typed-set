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
use function is_string;

/**
 * @package Jojo1981\TypedSet\TestSuite\Fixture
 */
final class StringHandler implements HandlerInterface
{
    /**
     * @param mixed $element
     * @param TypeInterface $type
     * @return bool
     */
    public function support(mixed $element, TypeInterface $type): bool
    {
        return is_string($element);
    }

    /**
     * @param mixed $element
     * @param TypeInterface $type
     * @return string
     */
    public function getHash(mixed $element, TypeInterface $type): string
    {
        return 'test-hash-generated-by-string-handler';
    }
}
