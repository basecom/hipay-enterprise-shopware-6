<?php

namespace Basecom\TemplatePlugin\Tests\Unit;

use Basecom\TemplateTheme\TemplateTheme;
use PHPUnit\Framework\TestCase;

class TemplateThemeTest extends TestCase
{
    /** @test */
    public function simpleTest(): void
    {
        $class = new TemplateTheme(true, __DIR__.'/../../src');
        self::assertSame('theme.json', $class->getThemeConfigPath());
    }

    public function getName(bool $withDataSet = true): string
    {
        return "TemplateThemeTest";
    }
}
