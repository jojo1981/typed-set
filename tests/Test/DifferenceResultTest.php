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
use Jojo1981\TypedSet\DifferenceResult;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * @package Jojo1981\TypedSet\TestSuite\Test
 */
class DifferenceResultTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testGetters(): void
    {
        $lhs = new Difference([], []);
        $rhs = new Difference([], []);
        $same = [];
        $areEqual = true;

        $differenceResult = new DifferenceResult($lhs, $rhs, $same, $areEqual);
        self::assertSame($lhs, $differenceResult->getLhs());
        self::assertSame($rhs, $differenceResult->getRhs());
        self::assertSame($same, $differenceResult->getSame());
        self::assertEquals($areEqual, $differenceResult->areEqual());
    }
}
