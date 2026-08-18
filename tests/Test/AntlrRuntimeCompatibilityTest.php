<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2026 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet\TestSuite\Test;

use Composer\InstalledVersions;
use Jojo1981\TypedSet\Exception\SetException;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;
use Jojo1981\TypedSet\Set;
use OutOfBoundsException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use function array_slice;
use function explode;
use function implode;
use function in_array;
use function restore_error_handler;
use function set_error_handler;
use const E_USER_WARNING;

/**
 * Guards the compatibility between this library and the installed antlr/antlr4-php-runtime version (^0.9 || ^0.10),
 * which is pulled in transitively through jojo1981/php-types and used for parsing the type given to a Set.
 *
 * @package Jojo1981\TypedSet\TestSuite\Test
 */
final class AntlrRuntimeCompatibilityTest extends TestCase
{
    /**
     * The antlr/antlr4-php-runtime versions this library is tested against (see .github/workflows/build.yml).
     */
    private const SUPPORTED_RUNTIME_VERSIONS = ['0.9', '0.10'];

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     * @throws ExpectationFailedException
     */
    public function testInstalledRuntimeVersionIsSupported(): void
    {
        $installedVersion = InstalledVersions::getPrettyVersion('antlr/antlr4-php-runtime') ?? '';
        self::assertTrue(
            in_array($this->getMajorMinorVersion($installedVersion), self::SUPPORTED_RUNTIME_VERSIONS, true),
            'The installed antlr/antlr4-php-runtime version: `' . $installedVersion . '` is not a supported'
            . ' version. Supported versions are: `^' . implode('` and `^', self::SUPPORTED_RUNTIME_VERSIONS) . '`.'
        );
    }

    /**
     * Creating a Set parses the given type using the parser code of jojo1981/php-types which has been generated
     * with the ANTLR tool. When the installed antlr/antlr4-php-runtime version is not compatible with that
     * generated parser code an E_USER_WARNING will be triggered by RuntimeMetaData::checkVersion.
     *
     * @return void
     * @throws SetException
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public function testCreatingASetDoesNotTriggerRuntimeVersionWarnings(): void
    {
        $triggeredWarnings = [];
        set_error_handler(
            static function (int $errorNumber, string $errorMessage) use (&$triggeredWarnings): bool {
                $triggeredWarnings[] = $errorMessage;

                return true;
            },
            E_USER_WARNING
        );

        try {
            $set = new Set('string', ['text1', 'text2']);
        } finally {
            restore_error_handler();
        }

        self::assertSame(['text1', 'text2'], $set->toArray());
        self::assertSame([], $triggeredWarnings);
    }

    /**
     * @param string $version
     * @return string
     */
    private function getMajorMinorVersion(string $version): string
    {
        return implode('.', array_slice(explode('.', $version), 0, 2));
    }
}
