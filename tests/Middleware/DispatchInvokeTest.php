<?php
/**
 * Weave Core.
 */
namespace Weave\Middleware;

use PHPUnit\Framework\TestCase;

class DispatchInvokeTest extends TestCase
{
	/**
	 * Test basic consturction with the mock objects works.
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
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn(false);

		$result = $dispatch(
			$request,
			$response,
			function ($request, $response) {
				return 'foo';
			}
		);
		$this->assertEquals('foo', $result);
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

		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn(false);

		$result = $dispatch(
			$request,
			function ($request, $response) {
				return 'foo';
			}
		);
		$this->assertEquals('foo', $result);
	}

	/**
	 * Test trying to dispatch successfully, double-pass style.
	 *
	 * @return null
	 */
	public function testDoublePassWithDispatch()
	{
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->method('shift')->willReturn(false);
		$resolveAdaptor->method('resolve')->willReturn('wibble');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->method('dispatch')->willReturn('ping');


		$dispatch = new Dispatch($resolveAdaptor, $dispatchAdaptor);

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn('bar');
		$request->method('withAttribute')->willReturn($request);

		$result = $dispatch(
			$request,
			$response,
			function ($request, $response) {
				return 'foo';
			}
		);
		$this->assertEquals('ping', $result);
	}

	/**
	 * Test trying to dispatch successfully, single-pass style.
	 *
	 * @return null
	 */
	public function testSinglePassWithDispatch()
	{
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->method('shift')->willReturn(false);
		$resolveAdaptor->method('resolve')->willReturn('wibble');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->method('dispatch')->willReturn('ping');


		$dispatch = new Dispatch($resolveAdaptor, $dispatchAdaptor);

		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn('bar');
		$request->method('withAttribute')->willReturn($request);

		$result = $dispatch(
			$request,
			function ($request) {
				return 'foo';
			}
		);
		$this->assertEquals('ping', $result);
	}

	/**
	 * Test trying to unsuccessful dispatch, double-pass style.
	 *
	 * @return null
	 */
	public function testDoublePassWithDispatchFail()
	{
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->method('shift')->willReturn(false);
		$resolveAdaptor->method('resolve')->willReturn('wibble');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->method('dispatch')->willReturn(false);


		$dispatch = new Dispatch($resolveAdaptor, $dispatchAdaptor);

		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn('bar');
		$request->method('withAttribute')->willReturn($request);

		$result = $dispatch(
			$request,
			$response,
			function ($request, $response) {
				return 'foo';
			}
		);
		$this->assertEquals('foo', $result);
	}

	/**
	 * Test trying to unsuccessful dispatch, single-pass style.
	 *
	 * @return null
	 */
	public function testSinglePassWithDispatchFail()
	{
		$resolveAdaptor = $this->createMock(\Weave\Resolve\ResolveAdaptorInterface::class);
		$resolveAdaptor->method('shift')->willReturn(false);
		$resolveAdaptor->method('resolve')->willReturn('wibble');

		$dispatchAdaptor = $this->createMock(\Weave\Dispatch\DispatchAdaptorInterface::class);
		$dispatchAdaptor->method('dispatch')->willReturn(false);


		$dispatch = new Dispatch($resolveAdaptor, $dispatchAdaptor);

		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn('bar');
		$request->method('withAttribute')->willReturn($request);

		$result = $dispatch(
			$request,
			function ($request) {
				return 'foo';
			}
		);
		$this->assertEquals('foo', $result);
	}
}
