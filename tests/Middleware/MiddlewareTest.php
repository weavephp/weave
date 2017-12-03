<?php
/**
 * Weave Core.
 */
namespace Weave\Middleware;

use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
	/**
	 * Test basic consturction with the mock objects works.
	 *
	 * @return null
	 */
	public function testConstruct()
	{
		$middlewareAdaptor = $this->createMock(MiddlewareAdaptorInterface::class);
		$middlewareAdaptor->method('setResolver');

		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);

		$requestFactory = $this->createMock(\Weave\Http\RequestFactoryInterface::class);
		$responseFactory = $this->createMock(\Weave\Http\ResponseFactoryInterface::class);
		$emitter = $this->createMock(\Weave\Http\ResponseEmitterInterface::class);

		$middleware = new Middleware(
			$middlewareAdaptor,
			function () {
				return 'pipelineFoo';
			},
			$resolveAdaptor,
			$dispatchAdaptor,
			$requestFactory,
			$responseFactory,
			$emitter
		);

		$this->assertInstanceOf(Middleware::class, $middleware);
	}

	/**
	 * Test chaining a new pipeline onto an existing pipeline.
	 *
	 * @return null
	 */
	public function testChain()
	{
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);

		$middlewareAdaptor = $this->createMock(MiddlewareAdaptorInterface::class);
		$middlewareAdaptor->method('setResolver');
		$middlewareAdaptor->method('executePipeline')
		->with(
			$this->equalTo('pipelineFoo'),
			$this->equalTo($request),
			$this->equalTo(null)
		)
		->willReturn('ping');

		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);

		$requestFactory = $this->createMock(\Weave\Http\RequestFactoryInterface::class);
		$responseFactory = $this->createMock(\Weave\Http\ResponseFactoryInterface::class);
		$emitter = $this->createMock(\Weave\Http\ResponseEmitterInterface::class);

		$middleware = new Middleware(
			$middlewareAdaptor,
			function () {
				return 'pipelineFoo';
			},
			$resolveAdaptor,
			$dispatchAdaptor,
			$requestFactory,
			$responseFactory,
			$emitter
		);

		$result = $middleware->chain($pipelineName, $request);

		$this->assertEquals('ping', $result);
	}

	/**
	 * Test setting up of an initial pipeline.
	 *
	 * @return null
	 */
	public function testExecute()
	{
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);

		$middlewareAdaptor = $this->createMock(MiddlewareAdaptorInterface::class);
		$middlewareAdaptor->method('setResolver');
		$middlewareAdaptor->method('isDoublePass')->willReturn(true);
		$middlewareAdaptor->method('executePipeline')
		->with(
			$this->equalTo('pipelineFoo'),
			$this->equalTo($request),
			$this->equalTo($response)
		)
		->willReturn('ping');

		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);

		$requestFactory = $this->createMock(\Weave\Http\RequestFactoryInterface::class);
		$requestFactory->method('newIncomingRequest')->willReturn($request);

		$responseFactory = $this->createMock(\Weave\Http\ResponseFactoryInterface::class);
		$responseFactory->method('newResponse')->willReturn($response);

		$emitter = $this->createMock(\Weave\Http\ResponseEmitterInterface::class);

		$middleware = new Middleware(
			$middlewareAdaptor,
			function () {
				return 'pipelineFoo';
			},
			$resolveAdaptor,
			$dispatchAdaptor,
			$requestFactory,
			$responseFactory,
			$emitter
		);

		$result = $middleware->execute();

		$this->assertEquals('ping', $result);
	}

	public function testEmit()
	{
		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);

		$middlewareAdaptor = $this->createMock(MiddlewareAdaptorInterface::class);
		$middlewareAdaptor->method('setResolver');

		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);

		$requestFactory = $this->createMock(\Weave\Http\RequestFactoryInterface::class);
		$responseFactory = $this->createMock(\Weave\Http\ResponseFactoryInterface::class);
		$emitter = $this->createMock(\Weave\Http\ResponseEmitterInterface::class);
		$emitter->expects($this->once())
		->method('emit')
		->with(
			$this->equalTo($response)
		);

		$middleware = new Middleware(
			$middlewareAdaptor,
			function () {
				return 'pipelineFoo';
			},
			$resolveAdaptor,
			$dispatchAdaptor,
			$requestFactory,
			$responseFactory,
			$emitter
		);

		$middleware->emit($response);
	}
}
