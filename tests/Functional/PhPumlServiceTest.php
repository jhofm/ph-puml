<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Test\Functional;

use Jhofm\PhPuml\Service\PhPumlService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class PhPumlServiceTest extends TestCase
{
    private $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = getContainer();
    }

    public function serviceTestDataProvider(): array
    {
        return Yaml::parseFile(__DIR__ . '/PhPumlServiceTest.yaml');
    }

    /**
     * @test
     * @dataProvider serviceTestDataProvider
     */
    public function testParseAssoc(string $dir, string $expected): void
    {
        $puml = $this->container->get(PhPumlService::class);
        $fixtureDir = __DIR__ . '/' . $dir;
        $out = $puml->generatePuml($fixtureDir);
        $this->assertSame($expected, $out);
    }
}
