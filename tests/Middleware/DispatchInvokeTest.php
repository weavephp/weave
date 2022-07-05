<?php

declare(strict_types = 1);

/**
 * Weave Core.
 */
namespace Weave\Middleware;

use PHPUnit\Framework\TestCase;

class DispatchInvokeTest extends TestCase
{
	/**
	 * Test basic construction with the mock objects works.
	 *
	 * @return null
	 */
	public function testConstruct()
	{
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);

		$dispatch = new Dispatch($resolveAdaptor, $dispatchAdaptor);

		$this->assertInstanceOf(Dispatch::class, $dispatch);
	}

	/**
	 * Test trying to dispatch, double-pass style, when there's nothing to process.
	 *
	 * @return null
	 */
	public function testDoublePassNoDispatch()
	{
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatch = new Dispatch($resolveAdaptor, $dispatchAdaptor);

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('foo');
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn(false);

		$result = $dispatch(
			$request,
			$response,
			fn ($request, $response) => $response
		);
		$this->assertEquals('foo', $result->getReasonPhrase());
	}

	/**
	 * Test trying to dispatch, single-pass style, when there's nothing to process.
	 *
	 * @return null
	 */
	public function testSinglePassNoDispatch()
	{
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatch = new Dispatch($resolveAdaptor, $dispatchAdaptor);

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('foo');
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn(false);

		$result = $dispatch(
			$request,
			fn ($request) => $response
		);
		$this->assertEquals('foo', $result->getReasonPhrase());
	}

	/**
	 * Test trying to dispatch successfully, double-pass style.
	 *
	 * @return null
	 */
	public function testDoublePassWithDispatch()
	{
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->method('shift')->willReturn([]);
		$resolveAdaptor->method('resolve')->willReturn(fn () => 'wibble');

		$response1 = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response1->method('getReasonPhrase')->willReturn('ping');
		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->method('dispatch')->willReturn($response1);


		$dispatch = new Dispatch($resolveAdaptor, $dispatchAdaptor);

		$response2 = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response2->method('getReasonPhrase')->willReturn('foo');
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn('bar');
		$request->method('withAttribute')->willReturn($request);

		$result = $dispatch(
			$request,
			$response2,
			fn ($request, $response) => $response
		);
		$this->assertEquals('ping', $result->getReasonPhrase());
	}

	/**
	 * Test trying to dispatch successfully, single-pass style.
	 *
	 * @return null
	 */
	public function testSinglePassWithDispatch()
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

		$result = $dispatch(
			$request,
			fn ($request) => 'foo'
		);
		$this->assertEquals('ping', $result->getReasonPhrase());
	}

	/**
	 * Test trying to unsuccessful dispatch, double-pass style.
	 *
	 * @return null
	 */
	public function testDoublePassWithDispatchFail()
	{
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->method('shift')->willReturn([]);
		$resolveAdaptor->method('resolve')->willReturn(fn () => 'wibble');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->method('dispatch')->willReturn(false);


		$dispatch = new Dispatch($resolveAdaptor, $dispatchAdaptor);

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('foo');
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn('bar');
		$request->method('withAttribute')->willReturn($request);

		$result = $dispatch(
			$request,
			$response,
			fn ($request, $response) => $response
		);
		$this->assertEquals('foo', $result->getReasonPhrase());
	}

	/**
	 * Test trying to unsuccessful dispatch, single-pass style.
	 *
	 * @return null
	 */
	public function testSinglePassWithDispatchFail()
	{
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->method('shift')->willReturn([]);
		$resolveAdaptor->method('resolve')->willReturn(fn () => 'wibble');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->method('dispatch')->willReturn(false);


		$dispatch = new Dispatch($resolveAdaptor, $dispatchAdaptor);

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response->method('getReasonPhrase')->willReturn('foo');

		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn('bar');
		$request->method('withAttribute')->willReturn($request);

		$result = $dispatch(
			$request,
			fn ($request) => $response
		);
		$this->assertEquals('foo', $result->getReasonPhrase());
	}
}
