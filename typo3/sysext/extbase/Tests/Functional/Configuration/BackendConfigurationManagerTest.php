<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\Extbase\Tests\Functional\Configuration;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class BackendConfigurationManagerTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function setConfigurationSetsExtensionAndPluginName(): void
    {
        $subject = $this->get(BackendConfigurationManager::class);
        $subject->setConfiguration([
            'extensionName' => 'SomeExtensionName',
            'pluginName' => 'SomePluginName',
        ]);
        self::assertEquals('SomeExtensionName', (new \ReflectionProperty($subject, 'extensionName'))->getValue($subject));
        self::assertEquals('SomePluginName', (new \ReflectionProperty($subject, 'pluginName'))->getValue($subject));
    }

    /**
     * @test
     */
    public function setConfigurationConvertsTypoScriptArrayToPlainArray(): void
    {
        $configuration = [
            'foo' => 'bar',
            'settings.' => ['foo' => 'bar'],
            'view.' => ['subkey.' => ['subsubkey' => 'subsubvalue']],
        ];
        $expectedResult = [
            'foo' => 'bar',
            'settings' => ['foo' => 'bar'],
            'view' => ['subkey' => ['subsubkey' => 'subsubvalue']],
        ];
        $subject = $this->get(BackendConfigurationManager::class);
        $subject->setConfiguration($configuration);
        self::assertEquals($expectedResult, (new \ReflectionProperty($subject, 'configuration'))->getValue($subject));
    }

    /**
     * @test
     */
    public function getConfigurationRecursivelyMergesCurrentExtensionConfigurationWithFrameworkConfiguration(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/BackendConfigurationManagerTestTypoScript.csv');
        $GLOBALS['TYPO3_REQUEST'] = new ServerRequest();
        $subject = $this->get(BackendConfigurationManager::class);
        $expectedResult = [
            'settings' => [
                'setting1' => 'overriddenValue1',
                'setting2' => 'value2',
                'setting3' => 'additionalValue1',
            ],
            'view' => [
                'viewSub' => [
                    'key1' => 'overridden1',
                    'key2' => 'value2',
                    'key3' => 'new key1',
                ],
            ],
            'persistence' => [
                'storagePid' => '123',
                'enableAutomaticCacheClearing' => '1',
                'updateReferenceIndex' => '0',
            ],
            'controllerConfiguration' => [],
            'mvc' => [
                'throwPageNotFoundExceptionIfActionCantBeResolved' => '0',
            ],
        ];
        self::assertEquals($expectedResult, $subject->getConfiguration('CurrentExtensionName'));
    }

    /**
     * @test
     */
    public function getConfigurationRecursivelyMergesCurrentPluginConfigurationWithFrameworkConfiguration(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/BackendConfigurationManagerTestTypoScript.csv');
        $GLOBALS['TYPO3_REQUEST'] = new ServerRequest();
        $subject = $this->get(BackendConfigurationManager::class);
        $expectedResult = [
            'settings' => [
                'setting1' => 'overriddenValue2',
                'setting2' => 'value2',
                'setting3' => 'additionalValue2',
            ],
            'view' => [
                'viewSub' => [
                    'key1' => 'overridden2',
                    'key2' => 'value2',
                    'key3' => 'new key2',
                ],
            ],
            'persistence' => [
                'storagePid' => '456',
                'enableAutomaticCacheClearing' => '1',
                'updateReferenceIndex' => '0',
            ],
            'controllerConfiguration' => [],
            'mvc' => [
                'throwPageNotFoundExceptionIfActionCantBeResolved' => '0',
            ],
        ];
        self::assertEquals($expectedResult, $subject->getConfiguration('CurrentExtensionName', 'CurrentPluginName'));
    }

    /**
     * @test
     */
    public function getCurrentPageIdReturnsPageIdFromGet(): void
    {
        $request = (new ServerRequest())->withQueryParams(['id' => 123]);
        $subject = $this->get(BackendConfigurationManager::class);
        $subject->setRequest($request);
        $getCurrentPageIdReflectionMethod = (new \ReflectionMethod($subject, 'getCurrentPageId'));
        $actualResult = $getCurrentPageIdReflectionMethod->invoke($subject);
        self::assertEquals(123, $actualResult);
    }

    /**
     * @test
     */
    public function getCurrentPageIdReturnsPageIdFromPost(): void
    {
        $request = (new ServerRequest())->withQueryParams(['id' => 123])->withParsedBody(['id' => 321]);
        $subject = $this->get(BackendConfigurationManager::class);
        $subject->setRequest($request);
        $getCurrentPageIdReflectionMethod = (new \ReflectionMethod($subject, 'getCurrentPageId'));
        $actualResult = $getCurrentPageIdReflectionMethod->invoke($subject);
        self::assertEquals(321, $actualResult);
    }

    /**
     * @test
     */
    public function getCurrentPageIdReturnsPidFromFirstRootTemplateIfIdIsNotSetAndNoRootPageWasFound(): void
    {
        (new ConnectionPool())->getConnectionForTable('sys_template')->insert(
            'sys_template',
            [
                'pid' => 123,
                'deleted' => 0,
                'hidden' => 0,
                'root' => 1,
            ]
        );
        $subject = $this->get(BackendConfigurationManager::class);
        $subject->setRequest(new ServerRequest());
        $getCurrentPageIdReflectionMethod = (new \ReflectionMethod($subject, 'getCurrentPageId'));
        $actualResult = $getCurrentPageIdReflectionMethod->invoke($subject);
        self::assertEquals(123, $actualResult);
    }

    /**
     * @test
     */
    public function getCurrentPageIdReturnsUidFromFirstRootPageIfIdIsNotSet(): void
    {
        (new ConnectionPool())->getConnectionForTable('pages')->insert(
            'pages',
            [
                'deleted' => 0,
                'hidden' => 0,
                'is_siteroot' => 1,
            ]
        );
        $subject = $this->get(BackendConfigurationManager::class);
        $subject->setRequest(new ServerRequest());
        $getCurrentPageIdReflectionMethod = (new \ReflectionMethod($subject, 'getCurrentPageId'));
        $actualResult = $getCurrentPageIdReflectionMethod->invoke($subject);
        self::assertEquals(1, $actualResult);
    }

    /**
     * @test
     */
    public function getCurrentPageIdReturnsDefaultStoragePidIfIdIsNotSetNoRootTemplateAndRootPageWasFound(): void
    {
        $subject = $this->get(BackendConfigurationManager::class);
        $subject->setRequest(new ServerRequest());
        $getCurrentPageIdReflectionMethod = (new \ReflectionMethod($subject, 'getCurrentPageId'));
        $actualResult = $getCurrentPageIdReflectionMethod->invoke($subject);
        self::assertEquals(0, $actualResult);
    }

    /**
     * @test
     */
    public function getRecursiveStoragePidsReturnsListOfPages(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/BackendConfigurationManagerRecursivePids.csv');
        $GLOBALS['BE_USER'] = new BackendUserAuthentication();
        $GLOBALS['BE_USER']->user = ['admin' => true];
        $subject = $this->get(BackendConfigurationManager::class);
        $getCurrentPageIdReflectionMethod = (new \ReflectionMethod($subject, 'getRecursiveStoragePids'));
        $actualResult = $getCurrentPageIdReflectionMethod->invoke($subject, [1, -6], 4);
        self::assertEquals([1, 2, 4, 5, 3, 6, 7], $actualResult);
    }
}
