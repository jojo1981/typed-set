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

use Jojo1981\PhpTypes\TypeInterface;
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
    public function testCanNotCompareSetsOfDifferenceType(): void
    {
        $exception = SetException::canNotCompareSetsOfDifferenceType();
        $this->assertEquals('Can not compare 2 sets of different types', $exception->getMessage());
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetExceptionDataProvider::getTestDataForDataIsNotOfExpectedType
     *
     * @param TypeInterface $expectedType
     * @param TypeInterface $actualType
     * @param string $expectedMessage
     * @param null|string $prefixMessage
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testDataIsNotOfExpectedType(
        TypeInterface $expectedType,
        TypeInterface $actualType,
        string $expectedMessage,
        ?string $prefixMessage = null
    ): void
    {
        $exception = SetException::dataIsNotOfExpectedType($expectedType, $actualType, $prefixMessage);
        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    /**
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testGivenTypeIsNotValidShouldReturnSetException(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = SetException::givenTypeIsNotValid('invalidType', $previous);
        $this->assertEquals(
            'Given type: `invalidType` is not a valid type and also not an existing class',
            $exception->getMessage()
        );
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testDeterminedTypeIsNotValidShouldReturnSetException(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = SetException::determinedTypeIsNotValid('null', $previous);
        $this->assertEquals(
            'Determined type: `null` is not a valid type and also not an existing class',
            $exception->getMessage()
        );
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testEmptyElementsCanNotDetermineTypeShouldReturnSetException(): void
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
    public function testTypeOmittedOnEmptySetShouldReturnSetException(): void
    {
        $exception = SetException::typeOmittedOnEmptySet();
        $this->assertEquals('Type can not be omitted on an empty Set', $exception->getMessage());
    }

    /**
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testCouldNotCreateTypeFromValueShouldReturnSetException(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = SetException::couldNotCreateTypeFromValue($previous);
        $this->assertEquals(
            'Could not create type from value',
            $exception->getMessage()
        );
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testCouldNotCreateTypeFromTypeNameShouldReturnSetException(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = SetException::couldNotCreateTypeFromTypeName('null', $previous);
        $this->assertEquals(
            'Could not create type from type name: `null`',
            $exception->getMessage()
        );
        $this->assertSame($previous, $exception->getPrevious());
    }
}