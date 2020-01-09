<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet\TestSuite\Test\Exception;

use Jojo1981\TypedSet\Exception\SetException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * @package Jojo1981\TypedSet\TestSuite\Test\Exception
 */
class SetExceptionTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testTypeIsNotValidShouldReturnCollectionException(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = SetException::typeIsNotValid('invalidType', $previous);
        $this->assertEquals(
            'Given type: `invalidType` is not a valid primitive type and also not an existing class',
            $exception->getMessage()
        );
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testEmptyElementsCanNotDetermineTypeShouldReturnCollectionException(): void
    {
        $exception = SetException::emptyElementsCanNotDetermineType();
        $this->assertEquals(
            'Elements can not be empty, because type can NOT be determined',
            $exception->getMessage()
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testCouldNotCreateTypeFromValueShouldReturnCollectionException(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = SetException::couldNotCreateTypeFromValue($previous);
        $this->assertEquals(
            'Could not create type from value',
            $exception->getMessage()
        );
        $this->assertSame($previous, $exception->getPrevious());
    }
}