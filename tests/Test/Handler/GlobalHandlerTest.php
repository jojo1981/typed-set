<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet\TestSuite\Test\Handler;

use DateTime;
use DateTimeInterface;
use Exception;
use Jojo1981\PhpTypes\AbstractType;
use Jojo1981\PhpTypes\Exception\TypeException;
use Jojo1981\PhpTypes\Value\Exception\ValueException;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;
use Jojo1981\TypedSet\Handler\GlobalHandler;
use Jojo1981\TypedSet\TestSuite\Fixture\Person;
use Jojo1981\TypedSet\TestSuite\Fixture\PersonHandler;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * @runInSeparateProcess
 * @preserveGlobalState disabled
 * @package Jojo1981\TypedSet\TestSuite\Test\Handler
 */
final class GlobalHandlerTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testIsSingleTon(): void
    {
        self::assertSame(GlobalHandler::getInstance(), GlobalHandler::getInstance());
    }

    /**
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws TypeException
     * @throws ValueException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testAddDefaultHandlers(): void
    {
        $element = new DateTime();
        $type = AbstractType::createFromTypeName(DateTimeInterface::class);
        self::assertFalse(GlobalHandler::getInstance()->support($element, $type));
        GlobalHandler::getInstance()->addDefaultHandlers();
        self::assertTrue(GlobalHandler::getInstance()->support($element, $type));
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws TypeException
     * @throws ValueException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testSupport(): void
    {
        $handler = new PersonHandler();
        $element = new Person('John Doe', 42);
        $type = AbstractType::createFromTypeName(Person::class);

        self::assertFalse(GlobalHandler::getInstance()->support($element, $type));
        GlobalHandler::getInstance()->registerHandler($handler);
        self::assertTrue(GlobalHandler::getInstance()->support($element, $type));
    }

    /**
     * @runInSeparateProcess
     *
     * @return void
     * @throws TypeException
     * @throws HandlerException
     */
    public function testGetHashWithUnsupportedElement(): void
    {
        $this->expectExceptionObject(HandlerException::canNotHandleElementBecauseNoHandlerAvailable());
        $element = new Person('John Doe', 42);
        $type = AbstractType::createFromTypeName(Person::class);
        GlobalHandler::getInstance()->getHash($element, $type);
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws TypeException
     * @throws ValueException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testGetHashWithSupportedElement(): void
    {
        $handler = new PersonHandler();
        $element = new Person('John Doe', 42);
        $type = AbstractType::createFromTypeName(Person::class);
        GlobalHandler::getInstance()->registerHandler($handler);
        self::assertEquals(
            'John Doe|42',
            GlobalHandler::getInstance()->getHash($element, $type)
        );
    }
}
