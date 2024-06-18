<?php

namespace Basecom\TemplateTheme\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;

class ExampleFunctionalTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testBrowser(): void
    {
        $browser = KernelLifecycleManager::createBrowser(KernelLifecycleManager::getKernel());

        $browser->request('GET', '/');

        self::assertSame(200, $browser->getResponse()->getStatusCode());
    }

    public function getName(bool $withDataSet = true): string
    {
        return "ExampleFunctionalTest";
    }
}
