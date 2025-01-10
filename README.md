A typed set data structure
=====================

[![PHP Status](https://github.com/jojo1981/typed-set/actions/workflows/build.yml/badge.svg)](https://github.com/jojo1981/typed-set/actions/workflows/build.yml)
[![Coverage Status](https://coveralls.io/repos/github/jojo1981/typed-set/badge.svg)](https://coveralls.io/github/jojo1981/typed-set)
[![Latest Stable Version](https://poser.pugx.org/jojo1981/typed-set/v/stable)](https://packagist.org/packages/jojo1981/typed-set)
[![Total Downloads](https://poser.pugx.org/jojo1981/typed-set/downloads)](https://packagist.org/packages/jojo1981/typed-set)
[![License](https://poser.pugx.org/jojo1981/typed-set/license)](https://packagist.org/packages/jojo1981/typed-set)

Author: Joost Nijhuis <[jnijhuis81@gmail.com](mailto:jnijhuis81@gmail.com)>

The typed set is an ordered mutable set.

When a new set is created the `type` for the set *MUST* be given.  
Also a set can be created based on elements by using: `\Jojo1981\TypedSet\Set::createFromElements`.  
The set is of a certain `type` and will guarantee all elements in the set are of the same `type`.  
The *type* can be a **primitive** `type` or a **class**/**interface** `type` set for the collection.

Elements are considered as equal when the hash is the same.  
The hash can be retrieved when the element is an instance of: `\Jojo1981\TypedSet\HashableInterface` or  
when there is a handler which support the type. Handlers are classes which are implementing  
the interface: `\Jojo1981\TypedSet\HandlerInterface` and are registered to the `GlobalHandler`.
The `HashableInterface` has a higher precedence than the handlers.

The `GlobalHandler` is a singleton which can be configured during the bootstrap of your application.  
Default handlers and custom handlers can be enabled.

When the element is an object which does not implement: `HashableInterface` and no handler support the  
element the fallback will be that a hash will be generated based on the object hash.  
This Set will be a set which has unique object instances.

Available types are:

- int (alias integer),
- float (aliases real, double or number)
- string (alias text)
- array
- object
- callable (alias callback)
- iterable
- class (class or interface name)

The `\Jojo1981\TypedSet\Set` class is countable and traversable (iterable).  
The set has the following convenient instance methods:

- add($element): void
- addAll(array $elements = []): void
- contains($element): bool
- remove($element): void
- clear(): void
- isEmpty(): bool
- isNonEmpty(): bool
- toArray(): array
- getType(): string
- isEqualType(TypeInterface $type): bool
- isEqual(Set $other): bool
- compare(Set $other): DifferenceResult
- map(callable $mapper, ?string $type = null): Set
- filter(callable $predicate): Set
- find(callable $predicate): mixed
- all(callable $predicate): bool
- some(callable $predicate): bool
- none(callable $predicate): bool
- count(): int

The `\Jojo1981\TypedSet\Set` has a static method `createFromElements`.

## Installation

### Library

```bash
git clone https://github.com/jojo1981/typed-set.git
```

### Composer

[Install PHP Composer](https://getcomposer.org/doc/00-intro.md)

```bash
composer require jojo1981/typed-set
```

## Basic usage

```php
<?php

require 'vendor/autoload.php';

// Some examples on how to construct a set

$set1 = new \Jojo1981\TypedSet\Set('string', ['text1', 'text2', 'text1']);
$set1->count(); // will return 2
$set1->contains('text1'); // will return true
$set1->contains('text3'); // will return false

$set2 = \Jojo1981\TypedSet\Set::createFromElements([1, 6, 7, 1, 2, 2, 9]);
$set2->count(); // will return 5
$set2->add(7);
$set2->count(); // will return 5
$set2->add(8);
$set2->count(); // will return 6

$set3 = new \Jojo1981\TypedSet\Set(\Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity::class);
$set3->addAll([new \Jojo1981\TypedSet\TestSuite\Fixture\TestEntity(), new \Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase()]);
$set1->count(); // will return 2
```

## Configuring the GlobalHandler
```php
<?php

require 'vendor/autoload.php';

// Optionally you can enable the default handlers before or after registering your own handlers.
\Jojo1981\TypedSet\Handler\GlobalHandler::getInstance()->addDefaultHandlers();

// Register a custom handler
\Jojo1981\TypedSet\Handler\GlobalHandler::getInstance()->registerHandler(new \Jojo1981\TypedSet\TestSuite\Fixture\PersonHandler());

```
 
