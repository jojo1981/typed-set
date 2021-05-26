<?php
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet\TestSuite\Test\Handler;

use Jojo1981\PhpTypes\AbstractType;
use Jojo1981\PhpTypes\Exception\TypeException;
use Jojo1981\TypedSet\Handler\DateTimeHandler;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;
use Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity;
use Jojo1981\TypedSet\TestSuite\Fixture\TestEntity;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * @package Jojo1981\TypedSet\TestSuite\Test\Handler
 */
class DateTimeHandlerTest extends TestCase
{
    /** @var DateTimeHandler */
    private $handler;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->handler = new DateTimeHandler();
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws TypeException
     * @return void
     */
    public function testSupport(): void
    {
        self::assertTrue($this->handler->support(new \DateTime(), AbstractType::createFromTypeName(\DateTimeInterface::class)));
        self::assertTrue($this->handler->support(new \DateTimeImmutable(), AbstractType::createFromTypeName(\DateTimeInterface::class)));
        self::assertFalse($this->handler->support(new \stdClass(), AbstractType::createFromTypeName(\stdClass::class)));
        self::assertFalse($this->handler->support(new TestEntity(), AbstractType::createFromTypeName(InterfaceTestEntity::class)));
    }

    /**
     * @throws HandlerException
     * @throws TypeException
     * @return void
     */
    public function testGetHashWithInvalidElement(): void
    {
        $this->expectExceptionObject(new HandlerException(
            'Can not handle element, element not an instance of: `\DateTimeInterface`, but an instance of: `\stdClass`'
        ));
        $this->handler->getHash(new \stdClass(), AbstractType::createFromTypeName(\stdClass::class));
    }

    /**
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws TypeException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testGetHash(): void
    {
        $element = new \DateTime('@1578591640');
        $expectedHash = 'cd5df504da36cca8e34fdb62b27d870051b7cd87175c149fffa9ccbb9d06284a';
        $actualHash = $this->handler->getHash($element, AbstractType::createFromTypeName(\DateTimeInterface::class));
        self::assertEquals($expectedHash, $actualHash);
    }
}
