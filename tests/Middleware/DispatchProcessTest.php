<?php

declare(strict_types = 1);

/**
 * Weave Core.
 */
namespace Weave\Middleware;

use PHPUnit\Framework\TestCase;

class DispatchProcessTest extends TestCase
{
	/**
	 * Test trying to dispatch when there's nothing to process.
	 *
	 * @return null
	 */
	public function testProcessNoDispatchNextHandle()
	{
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatch = new Dispatch($resolveAdaptor, $dispatchAdaptor);

		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn(false);

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('foo');

		$result = $dispatch->process(
			$request,
			new DispatchProcessTestHandleClass($response)
		);
		$this->assertEquals('foo', $result->getReasonPhrase());
	}

	/**
	 * Test trying to dispatch when there's nothing to process.
	 *
	 * @return null
	 */
	public function testProcessNoDispatchNextProcess()
	{
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatch = new Dispatch($resolveAdaptor, $dispatchAdaptor);

		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn(false);


		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('foo');

		$result = $dispatch->process(
			$request,
			new DispatchProcessTestProcessClass($response)
		);
		$this->assertEquals('foo', $result->getReasonPhrase());
	}

	/**
	 * Test trying to dispatch successfully.
	 *
	 * @return null
	 */
	public function testWithDispatch()
	{
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->method('shift')->willReturn([]);
		$resolveAdaptor->method('resolve')->willReturn(fn () => 'wibble');


		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('ping');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->method('dispatch')->willReturn($response);

		$dispatch = new Dispatch($resolveAdaptor, $dispatchAdaptor);

		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn('bar');
		$request->method('withAttribute')->willReturn($request);

		$result = $dispatch->process(
			$request,
			null
		);
		$this->assertEquals('ping', $result->getReasonPhrase());
	}

	/**
	 * Test trying to process unsuccessful dispatch.
	 *
	 * @return null
	 */
	public function testDispatchFailNextProcess()
	{
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->method('shift')->willReturn([]);
		$resolveAdaptor->method('resolve')->willReturn(fn () => 'wibble');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->method('dispatch')->willReturn(false);

		$dispatch = new Dispatch($resolveAdaptor, $dispatchAdaptor);

		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn('bar');
		$request->method('withAttribute')->willReturn($request);

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('foo');

		$result = $dispatch->process(
			$request,
			new DispatchProcessTestProcessClass($response)
		);
		$this->assertEquals('foo', $result->getReasonPhrase());
	}

	/**
	 * Test trying to process unsuccessful dispatch.
	 *
	 * @return null
	 */
	public function testDispatchFailNextHandle()
	{
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->method('shift')->willReturn([]);
		$resolveAdaptor->method('resolve')->willReturn(fn () => 'wibble');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->method('dispatch')->willReturn(false);

		$dispatch = new Dispatch($resolveAdaptor, $dispatchAdaptor);

		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn('bar');
		$request->method('withAttribute')->willReturn($request);

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('foo');

		$result = $dispatch->process(
			$request,
			new DispatchProcessTestHandleClass($response)
		);
		$this->assertEquals('foo', $result->getReasonPhrase());
	}
}
