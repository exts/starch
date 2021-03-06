<?php

namespace Starch\Tests\Unit\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Starch\Middleware\StackItem;
use PHPUnit\Framework\TestCase;
use Starch\Router\Route;

class StackItemTest extends TestCase
{
    /**
     * @var MiddlewareInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $middleware;

    /**
     * @var Route|PHPUnit_Framework_MockObject_MockObject
     */
    private $route;

    public function setUp()
    {
        $this->middleware = $this->createMock(MiddlewareInterface::class);
        $this->route = $this->createMock(Route::class);
    }

    public function testExecutesWithoutContstraint()
    {
        $this->route->expects($this->never())
            ->method('getPath');

        $item = new StackItem($this->middleware);

        $this->assertTrue($item->executeFor($this->route));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testExecutesWithConstraint(string $path, string $constraint, bool $result)
    {
        $this->route->expects($this->once())
                      ->method('getPath')
            ->willReturn($path);

        $item = new StackItem($this->middleware, $constraint);

        $this->assertEquals($result, $item->executeFor($this->route));
    }

    public function dataProvider()
    {
        return [
            ['/', '/', true],
            ['/', '/foo', false],
            ['/foo', '/', true],
            ['/foo/bar', '/foo', true],
            ['/foo', 'foo', false],
        ];
    }
}
