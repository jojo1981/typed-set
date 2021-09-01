<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet\Handler;

use Jojo1981\PhpTypes\TypeInterface;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;
use Jojo1981\TypedSet\HandlerInterface;
use function get_class;

/**
 * A composite and singleton class.
 *
 * @package Jojo1981\TypedSet\Handler
 */
final class GlobalHandler implements HandlerInterface
{
    /** @var HandlerInterface[]  */
    private array $handlers = [];

    /**
     * Private constructor, prevent getting an instance of this class using the new keyword from outside the lexical scope of this class.
     */
    private function __construct()
    {
        // Nothing to do here
    }

    /**
     * @return GlobalHandler
     */
    public static function getInstance(): GlobalHandler
    {
        static $handler;
        if (!isset($handler)) {
            $handler = new self();
        }

        return $handler;
    }

    /**
     * @return void
     */
    public function addDefaultHandlers(): void
    {
        $this->registerHandler(new DateTimeHandler());
    }

    /**
     * @param mixed $element
     * @param TypeInterface $type
     * @return bool
     */
    public function support($element, TypeInterface $type): bool
    {
        foreach ($this->handlers as $handler) {
            if ($handler->support($element, $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $element
     * @param TypeInterface $type
     * @throws HandlerException
     * @return string
     */
    public function getHash($element, TypeInterface $type): string
    {
        foreach ($this->handlers as $handler) {
            if ($handler->support($element, $type)) {
                return $handler->getHash($element, $type);
            }
        }

        throw HandlerException::canNotHandleElementBecauseNoHandlerAvailable();
    }

    /**
     * @param HandlerInterface $handler
     * @return void
     */
    public function registerHandler(HandlerInterface $handler): void
    {
        $this->handlers[get_class($handler)] = $handler;
    }
}
