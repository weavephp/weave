<?php

declare(strict_types = 1);

/**
 * Weave Core.
 */
namespace Weave\Middleware;

use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
	/**
	 * Test basic construction with the mock objects works.
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
			fn () => 'pipelineFoo',
			$resolveAdaptor,
			$dispatchAdaptor,
			$requestFactory,
			$responseFactory,
			$emitter
		);

		$this->assertInstanceOf(Middleware::class, $middleware);
	}

	/**
	 * Test basic consturction with the mock objects works.
	 *
	 * @return null
	 */
	public function testResolveClosure()
	{
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);

		$middlewareAdaptor = $this->createMock(MiddlewareAdaptorInterface::class);
		$middlewareAdaptor->method('setResolver')
		->with(
			$this->callback(
				function ($callable) use ($request) {
					$dispatchable = $callable('foo');
					$this->assertEquals('pong', $dispatchable($request));
					return true;
				}
			)
		);

		$fn = fn () => 'bar';

		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->expects($this->once())
		->method('resolve')
		->with($this->equalTo('foo'))
		->willReturn($fn);

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->expects($this->once())
		->method('dispatch')
		->with(
			$this->equalTo($fn),
			$this->equalTo(null),
			$this->equalTo(\Weave\Dispatch\DispatchAdaptorInterface::SOURCE_MIDDLEWARE_STACK),
			$this->equalTo($request)
		)
		->willReturn('pong');

		$requestFactory = $this->createMock(\Weave\Http\RequestFactoryInterface::class);
		$responseFactory = $this->createMock(\Weave\Http\ResponseFactoryInterface::class);
		$emitter = $this->createMock(\Weave\Http\ResponseEmitterInterface::class);

		$middleware = new Middleware(
			$middlewareAdaptor,
			fn () => 'pipelineFoo',
			$resolveAdaptor,
			$dispatchAdaptor,
			$requestFactory,
			$responseFactory,
			$emitter
		);
	}

	/**
	 * Test chaining a new pipeline onto an existing pipeline.
	 *
	 * @return null
	 */
	public function testChain()
	{
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('ping');

		$middlewareAdaptor = $this->createMock(MiddlewareAdaptorInterface::class);
		$middlewareAdaptor->method('setResolver');
		$middlewareAdaptor->method('executePipeline')
		->with(
			$this->equalTo('pipelineFoo'),
			$this->equalTo($request),
			$this->equalTo(null)
		)
		->willReturn($response);

		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);

		$requestFactory = $this->createMock(\Weave\Http\RequestFactoryInterface::class);
		$responseFactory = $this->createMock(\Weave\Http\ResponseFactoryInterface::class);
		$emitter = $this->createMock(\Weave\Http\ResponseEmitterInterface::class);

		$middleware = new Middleware(
			$middlewareAdaptor,
			fn () => 'pipelineFoo',
			$resolveAdaptor,
			$dispatchAdaptor,
			$requestFactory,
			$responseFactory,
			$emitter
		);

		$result = $middleware->chain('pipelineFoo', $request);

		$this->assertEquals('ping', $result->getReasonPhrase());
	}

	/**
	 * Test setting up of an initial double-pass pipeline.
	 *
	 * @return null
	 */
	public function testExecuteDoublePass()
	{
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);

		$response1 = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response1->method('getReasonPhrase')->willReturn('ping');

		$response2 = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response2->method('getReasonPhrase')->willReturn('wibble');

		$middlewareAdaptor = $this->createMock(MiddlewareAdaptorInterface::class);
		$middlewareAdaptor->method('setResolver');
		$middlewareAdaptor->method('isDoublePass')->willReturn(true);
		$middlewareAdaptor->method('executePipeline')
		->with(
			$this->equalTo('pipelineFoo'),
			$this->equalTo($request),
			$this->equalTo($response1)
		)
		->willReturn($response1);

		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);

		$requestFactory = $this->createMock(\Weave\Http\RequestFactoryInterface::class);
		$requestFactory->method('newIncomingRequest')->willReturn($request);

		$responseFactory = $this->createMock(\Weave\Http\ResponseFactoryInterface::class);
		$responseFactory->method('newResponse')->willReturn($response2);

		$emitter = $this->createMock(\Weave\Http\ResponseEmitterInterface::class);

		$middleware = new Middleware(
			$middlewareAdaptor,
			fn () => 'pipelineFoo',
			$resolveAdaptor,
			$dispatchAdaptor,
			$requestFactory,
			$responseFactory,
			$emitter
		);

		$result = $middleware->execute();

		$this->assertEquals('ping', $result->getReasonPhrase());
	}

	/**
	 * Test setting up of an initial single-pass pipeline.
	 *
	 * @return null
	 */
	public function testExecuteSinglePass()
	{
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('ping');

		$middlewareAdaptor = $this->createMock(MiddlewareAdaptorInterface::class);
		$middlewareAdaptor->method('setResolver');
		$middlewareAdaptor->method('isDoublePass')->willReturn(false);
		$middlewareAdaptor->method('executePipeline')
		->with(
			$this->equalTo('pipelineFoo'),
			$this->equalTo($request),
			$this->equalTo(null)
		)
		->willReturn($response);

		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);

		$requestFactory = $this->createMock(\Weave\Http\RequestFactoryInterface::class);
		$requestFactory->method('newIncomingRequest')->willReturn($request);

		$responseFactory = $this->createMock(\Weave\Http\ResponseFactoryInterface::class);
		$responseFactory->expects($this->never())->method('newResponse');

		$emitter = $this->createMock(\Weave\Http\ResponseEmitterInterface::class);

		$middleware = new Middleware(
			$middlewareAdaptor,
			fn () => 'pipelineFoo',
			$resolveAdaptor,
			$dispatchAdaptor,
			$requestFactory,
			$responseFactory,
			$emitter
		);

		$result = $middleware->execute();

		$this->assertEquals('ping', $result->getReasonPhrase());
	}

	/**
	 * Test calling the emitter on emit.
	 *
	 * @return null
	 */
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
			fn () => 'pipelineFoo',
			$resolveAdaptor,
			$dispatchAdaptor,
			$requestFactory,
			$responseFactory,
			$emitter
		);

		$middleware->emit($response);
	}
}
