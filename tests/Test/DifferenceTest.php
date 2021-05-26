<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet\TestSuite\Test;

use Jojo1981\TypedSet\Difference;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * @package Jojo1981\TypedSet\TestSuite\Test
 */
class DifferenceTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testGetMissingElements(): void
    {
        $missingElements = ['item1', 'item2', 'item3'];
        $difference = new Difference($missingElements, []);
        self::assertSame($missingElements, $difference->getMissingElements());
        self::assertEquals([], $difference->getExtraElements());
    }

    /**
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testGetExtraElements(): void
    {
        $extraElements = ['item1', 'item2', 'item3'];
        $difference = new Difference([], $extraElements);
        self::assertSame($extraElements, $difference->getExtraElements());
        self::assertEquals([], $difference->getMissingElements());
    }
}
