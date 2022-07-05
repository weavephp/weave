<?php

declare(strict_types = 1);

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
			$this->equalTo(['pong'])
		)
		->willReturn($request);

		$response1 = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response2 = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response2->method('getReasonPhrase')->willReturn('bar');

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
		->willReturn(['pong']);

		$fn = fn () => 'foo';
		$resolveAdaptor->expects($this->once())
		->method('resolve')
		->with($this->equalTo('ping'))
		->willReturn($fn);

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->expects($this->once())
		->method('dispatch')
		->with(
			$this->equalTo($fn),
			$this->equalTo(null),
			$this->equalTo(\Weave\Dispatch\DispatchAdaptorInterface::SOURCE_ROUTER),
			$this->equalTo($request),
			$this->equalTo($response1)
		)
		->willReturn($response2);

		$router = new Router(
			$routerAdaptor,
			function () {
			},
			$resolveAdaptor,
			$dispatchAdaptor
		);

		$result = $router(
			$request,
			$response1,
			function () {
			}
		);

		$this->assertEquals('bar', $result->getReasonPhrase());
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
			$this->equalTo(['pong'])
		)
		->willReturn($request);

		$fn = fn () => 'foo';

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('bar');

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
		->willReturn(['pong']);

		$resolveAdaptor->expects($this->once())
		->method('resolve')
		->with($this->equalTo('ping'))
		->willReturn($fn);

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->expects($this->once())
		->method('dispatch')
		->with(
			$this->equalTo($fn),
			$this->equalTo(null),
			$this->equalTo(\Weave\Dispatch\DispatchAdaptorInterface::SOURCE_ROUTER),
			$this->equalTo($request)
		)
		->willReturn($response);

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

		$this->assertEquals('bar', $result->getReasonPhrase());
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
		$response2 = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response2->method('getReasonPhrase')->willReturn('foo');

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
			function ($incomingRequest, $incomingResponse) use ($request, $response, $response2) {
				$this->assertEquals($request, $incomingRequest);
				$this->assertEquals($response, $incomingResponse);
				return $response2;
			}
		);

		$this->assertEquals('foo', $result->getReasonPhrase());
	}

	/**
	 * Check a single-pass style, invoke-based failed route works.
	 */
	public function testInvokeSinglePassRouteFail()
	{
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->expects($this->never())
		->method('withAttribute');

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('foo');

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
			function ($incomingRequest) use ($request, $response) {
				$this->assertEquals($request, $incomingRequest);
				return $response;
			}
		);

		$this->assertEquals('foo', $result->getReasonPhrase());
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
			$this->equalTo(['pong'])
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
		->willReturn(['pong']);

		$fn = fn () => 'foo';
		$resolveAdaptor->expects($this->once())
		->method('resolve')
		->with($this->equalTo('ping'))
		->willReturn($fn);

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('bar');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->expects($this->once())
		->method('dispatch')
		->with(
			$this->equalTo($fn),
			$this->equalTo(null),
			$this->equalTo(\Weave\Dispatch\DispatchAdaptorInterface::SOURCE_ROUTER),
			$this->equalTo($request)
		)
		->willReturn($response);

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

		$this->assertEquals('bar', $result->getReasonPhrase());
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

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('ping');

		$result = $router->process(
			$request,
			new RouterTestHandleClass($response)
		);

		$this->assertEquals('ping', $result->getReasonPhrase());
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

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('ping');

		$result = $router->process(
			$request,
			new RouterTestProcessClass($response)
		);

		$this->assertEquals('ping', $result->getReasonPhrase());
	}
}
