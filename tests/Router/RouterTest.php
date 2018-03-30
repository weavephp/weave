<?php
/**
 * Weave Core.
 */
namespace Weave\Router;

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
	/**
	 * Test basic construction with the mock objects works.
	 *
	 * @return null
	 */
	public function testConstruct()
	{
		$routerAdaptor = $this->createMock(RouterAdaptorInterface::class);
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);

		$router = new Router(
			$routerAdaptor,
			function () {
			},
			$resolveAdaptor,
			$dispatchAdaptor
		);

		$this->assertInstanceOf(Router::class, $router);
	}

	/**
	 * Check a double-pass style, invoke-based successful route works.
	 */
	public function testInvokeDoublePassRouteSuccess()
	{
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->expects($this->once())
		->method('withAttribute')
		->with(
			$this->equalTo('dispatch.handler'),
			$this->equalTo('pong')
		)
		->willReturn($request);

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);

		$routerAdaptor = $this->createMock(RouterAdaptorInterface::class);
		$routerAdaptor->expects($this->once())->method('configureRoutes');
		$routerAdaptor->expects($this->once())
		->method('route')
		->with($this->equalTo($request))
		->willReturn('ping');

		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->expects($this->once())
		->method('shift')
		->with($this->equalTo('ping'))
		->willReturn('pong');

		$resolveAdaptor->expects($this->once())
		->method('resolve')
		->with($this->equalTo('ping'))
		->willReturn('foo');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->expects($this->once())
		->method('dispatch')
		->with(
			$this->equalTo('foo'),
			$this->equalTo(null),
			$this->equalTo(\Weave\Dispatch\DispatchAdaptorInterface::SOURCE_ROUTER),
			$this->equalTo($request),
			$this->equalTo($response)
		)
		->willReturn('bar');

		$router = new Router(
			$routerAdaptor,
			function () {
			},
			$resolveAdaptor,
			$dispatchAdaptor
		);

		$result = $router(
			$request,
			$response,
			function () {
			}
		);

		$this->assertEquals('bar', $result);
	}

	/**
	 * Check a single-pass style, invoke-based successful route works.
	 */
	public function testInvokeSinglePassRouteSuccess()
	{
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->expects($this->once())
		->method('withAttribute')
		->with(
			$this->equalTo('dispatch.handler'),
			$this->equalTo('pong')
		)
		->willReturn($request);

		$routerAdaptor = $this->createMock(RouterAdaptorInterface::class);
		$routerAdaptor->expects($this->once())->method('configureRoutes');
		$routerAdaptor->expects($this->once())
		->method('route')
		->with($this->equalTo($request))
		->willReturn('ping');

		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->expects($this->once())
		->method('shift')
		->with($this->equalTo('ping'))
		->willReturn('pong');

		$resolveAdaptor->expects($this->once())
		->method('resolve')
		->with($this->equalTo('ping'))
		->willReturn('foo');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->expects($this->once())
		->method('dispatch')
		->with(
			$this->equalTo('foo'),
			$this->equalTo(null),
			$this->equalTo(\Weave\Dispatch\DispatchAdaptorInterface::SOURCE_ROUTER),
			$this->equalTo($request)
		)
		->willReturn('bar');

		$router = new Router(
			$routerAdaptor,
			function () {
			},
			$resolveAdaptor,
			$dispatchAdaptor
		);

		$result = $router(
			$request,
			function () {
			}
		);

		$this->assertEquals('bar', $result);
	}

	/**
	 * Check a double-pass style, invoke-based failed route works.
	 */
	public function testInvokeDoublePassRouteFail()
	{
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->expects($this->never())
		->method('withAttribute');

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);

		$routerAdaptor = $this->createMock(RouterAdaptorInterface::class);
		$routerAdaptor->expects($this->once())->method('configureRoutes');
		$routerAdaptor->expects($this->once())
		->method('route')
		->with($this->equalTo($request))
		->willReturn(false);

		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->expects($this->never())->method('shift');

		$resolveAdaptor->expects($this->never())->method('resolve');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->expects($this->never())
		->method('dispatch');

		$router = new Router(
			$routerAdaptor,
			function () {
			},
			$resolveAdaptor,
			$dispatchAdaptor
		);

		$result = $router(
			$request,
			$response,
			function ($incomingRequest, $incomingResponse) use ($request, $response) {
				$this->assertEquals($request, $incomingRequest);
				$this->assertEquals($response, $incomingResponse);
				return 'foo';
			}
		);

		$this->assertEquals('foo', $result);
	}

	/**
	 * Check a single-pass style, invoke-based failed route works.
	 */
	public function testInvokeSinglePassRouteFail()
	{
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->expects($this->never())
		->method('withAttribute');

		$routerAdaptor = $this->createMock(RouterAdaptorInterface::class);
		$routerAdaptor->expects($this->once())->method('configureRoutes');
		$routerAdaptor->expects($this->once())
		->method('route')
		->with($this->equalTo($request))
		->willReturn(false);

		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->expects($this->never())->method('shift');

		$resolveAdaptor->expects($this->never())->method('resolve');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->expects($this->never())
		->method('dispatch');

		$router = new Router(
			$routerAdaptor,
			function () {
			},
			$resolveAdaptor,
			$dispatchAdaptor
		);

		$result = $router(
			$request,
			function ($incomingRequest) use ($request) {
				$this->assertEquals($request, $incomingRequest);
				return 'foo';
			}
		);

		$this->assertEquals('foo', $result);
	}

	/**
	 * Check a single-pass style, process-call-based successful route works.
	 */
	public function testProcessSinglePassRouteSuccess()
	{
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->expects($this->once())
		->method('withAttribute')
		->with(
			$this->equalTo('dispatch.handler'),
			$this->equalTo('pong')
		)
		->willReturn($request);

		$routerAdaptor = $this->createMock(RouterAdaptorInterface::class);
		$routerAdaptor->expects($this->once())->method('configureRoutes');
		$routerAdaptor->expects($this->once())
		->method('route')
		->with($this->equalTo($request))
		->willReturn('ping');

		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->expects($this->once())
		->method('shift')
		->with($this->equalTo('ping'))
		->willReturn('pong');

		$resolveAdaptor->expects($this->once())
		->method('resolve')
		->with($this->equalTo('ping'))
		->willReturn('foo');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->expects($this->once())
		->method('dispatch')
		->with(
			$this->equalTo('foo'),
			$this->equalTo(null),
			$this->equalTo(\Weave\Dispatch\DispatchAdaptorInterface::SOURCE_ROUTER),
			$this->equalTo($request)
		)
		->willReturn('bar');

		$router = new Router(
			$routerAdaptor,
			function () {
			},
			$resolveAdaptor,
			$dispatchAdaptor
		);

		$result = $router->process(
			$request,
			null
		);

		$this->assertEquals('bar', $result);
	}

	/**
	 * Check a single-pass, process-call-based failed route works for a 'handle' based next.
	 */
	public function testProcessSinglePassRouteFailHandle()
	{
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->expects($this->never())
		->method('withAttribute');

		$routerAdaptor = $this->createMock(RouterAdaptorInterface::class);
		$routerAdaptor->expects($this->once())->method('configureRoutes');
		$routerAdaptor->expects($this->once())
		->method('route')
		->with($this->equalTo($request))
		->willReturn(false);

		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->expects($this->never())->method('shift');

		$resolveAdaptor->expects($this->never())->method('resolve');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->expects($this->never())
		->method('dispatch');

		$router = new Router(
			$routerAdaptor,
			function () {
			},
			$resolveAdaptor,
			$dispatchAdaptor
		);

		$result = $router->process(
			$request,
			new RouterTestHandleClass()
		);

		$this->assertEquals('ping', $result);
	}

	/**
	 * Check a single-pass, process-call-based failed route works for a 'process' based next.
	 */
	public function testProcessSinglePassRouteFailProcess()
	{
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->expects($this->never())
		->method('withAttribute');

		$routerAdaptor = $this->createMock(RouterAdaptorInterface::class);
		$routerAdaptor->expects($this->once())->method('configureRoutes');
		$routerAdaptor->expects($this->once())
		->method('route')
		->with($this->equalTo($request))
		->willReturn(false);

		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->expects($this->never())->method('shift');

		$resolveAdaptor->expects($this->never())->method('resolve');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->expects($this->never())
		->method('dispatch');

		$router = new Router(
			$routerAdaptor,
			function () {
			},
			$resolveAdaptor,
			$dispatchAdaptor
		);

		$result = $router->process(
			$request,
			new RouterTestProcessClass()
		);

		$this->assertEquals('ping', $result);
	}
}
