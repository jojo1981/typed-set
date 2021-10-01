<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet\TestSuite\Test\Handler\Exception;

use Jojo1981\PhpTypes\AbstractType;
use Jojo1981\PhpTypes\Exception\TypeException;
use Jojo1981\PhpTypes\TypeInterface;
use Jojo1981\PhpTypes\Value\Exception\ValueException;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use stdClass;

/**
 * @package Jojo1981\TypedSet\TestSuite\Test\Handler\Exception
 */
final class HandlerExceptionTest extends TestCase
{
    /**
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testCanNotHandleElementWithoutTypes(): void
    {
        $exception = HandlerException::canNotHandleElement();
        self::assertEquals('Can not handle element', $exception->getMessage());
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws TypeException
     * @throws ValueException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testCanNotHandleElementOnlyWithActualType(): void
    {
        $exception = HandlerException::canNotHandleElement(null, AbstractType::createFromTypeName('string'));
        self::assertEquals('Can not handle element', $exception->getMessage());
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws TypeException
     * @throws ValueException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testCanNotHandleElementOnlyWithExpectedType(): void
    {
        $exception1 = HandlerException::canNotHandleElement(AbstractType::createFromTypeName('string'));
        self::assertEquals(
            'Can not handle element, element not of type: `string`',
            $exception1->getMessage()
        );

        $exception2 = HandlerException::canNotHandleElement(AbstractType::createFromTypeName(stdClass::class));
        self::assertEquals(
            'Can not handle element, element not an instance of: `stdClass`',
            $exception2->getMessage()
        );
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\HandlerExceptionDataProvider::getTestData()
     *
     * @param TypeInterface $expectedType
     * @param TypeInterface $actualType
     * @param string $expectedMessage
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testCanNotHandleElementOnlyWithBothTypes(
        TypeInterface $expectedType,
        TypeInterface $actualType,
        string $expectedMessage
    ): void {
        $exception = HandlerException::canNotHandleElement($expectedType, $actualType);
        self::assertEquals($expectedMessage, $exception->getMessage());
    }

    /**
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testCanNotHandleElementBecauseNoHandlerAvailable(): void
    {
        $exception = HandlerException::canNotHandleElementBecauseNoHandlerAvailable();
        self::assertEquals(
            'Can not handle the element, because there is no handler registered which support this element',
            $exception->getMessage()
        );
    }
}
