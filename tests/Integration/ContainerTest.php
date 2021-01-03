<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Test\Integration;

use Exception;
use Jhofm\PhPuml\Service\PhPumlService;
use PHPUnit\Framework\TestCase;
use ProjectServiceContainer;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ContainerTest
 */
class ContainerTest extends TestCase
{
    /** @var ProjectServiceContainer */
    private $subject;

    /**
     * @throws Exception
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = getContainer();
    }

    /**
     * @throws Exception
     * @return void
     */
    public function testGetApplication()
    {
        $application = $this->subject->get(Application::class);
        $this->assertInstanceOf(Application::class, $application);
    }

    /**
     * @throws Exception
     * @return void
     */
    public function testGetPrivateServiceThrowsException()
    {
        $this->expectException(ServiceNotFoundException::class);
        $this->subject->get(PhPumlService::class);
    }
}
