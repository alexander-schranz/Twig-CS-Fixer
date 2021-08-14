<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Config;

use Exception;
use PHPUnit\Framework\TestCase;
use TwigCsFixer\Config\ConfigResolver;

/**
 * Test for ConfigResolver.
 */
class ConfigResolverTest extends TestCase
{
    /**
     * @param string      $workingDir
     * @param string|null $path
     * @param string      $configName
     *
     * @return void
     *
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig(string $workingDir, ?string $path, string $configName): void
    {
        $configResolver = new ConfigResolver($workingDir);
        $config = $configResolver->getConfig($path);

        self::assertSame($configName, $config->getName());
    }

    /**
     * @return iterable<array-key, array{string, string|null, string}>
     */
    public function getConfigDataProvider(): iterable
    {
        yield [__DIR__.'/Fixtures/directoryWithoutConfig', null, 'Default'];
        yield [__DIR__.'/Fixtures/directoryWithConfig', null, 'Custom'];
        yield [__DIR__, 'Fixtures/directoryWithConfig/.twig-cs-fixer.php', 'Custom'];
        yield ['/tmp', __DIR__.'/Fixtures/directoryWithConfig/.twig-cs-fixer.php', 'Custom'];
    }

    /**
     * @param string      $workingDir
     * @param string|null $path
     *
     * @return void
     *
     * @dataProvider getConfigExceptionDataProvider
     */
    public function testGetConfigException(string $workingDir, ?string $path): void
    {
        $configResolver = new ConfigResolver($workingDir);

        self::expectException(Exception::class);
        $configResolver->getConfig($path);
    }

    /**
     * @return iterable<array-key, array{string, string|null}>
     */
    public function getConfigExceptionDataProvider(): iterable
    {
        yield [__DIR__.'/Fixtures/directoryWithInvalidConfig', null];
        yield [__DIR__, 'Fixtures/directoryWithInvalidConfig/.twig-cs-fixer.php'];
        yield [__DIR__, 'Fixtures/path/to/not/found/.twig-cs-fixer.php'];
    }

    /**
     * @param string $configPath
     * @param string $path
     *
     * @return void
     *
     * @dataProvider configPathIsCorrectlyGeneratedDataProvider
     */
    public function testConfigPathIsCorrectlyGenerated(string $configPath, string $path): void
    {
        $configResolver = new ConfigResolver('/tmp/path/not/found');

        self::expectExceptionMessage(sprintf('Cannot find the config file "%s".', $configPath));
        $configResolver->getConfig($path);
    }

    /**
     * @return iterable<array-key, array{string, string}>
     */
    public function configPathIsCorrectlyGeneratedDataProvider(): iterable
    {
        yield ['/tmp/path/not/found/', ''];
        yield ['/tmp/path/not/found/a', 'a'];
        yield ['/tmp/path/not/found/../a', '../a'];
        yield ['/a', '/a'];
        yield ['\\a', '\\a'];
        yield ['C:\WINDOWS', 'C:\WINDOWS'];
    }
}