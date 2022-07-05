<?php

declare(strict_types = 1);

/**
 * Weave Core.
 */
namespace Weave\Resolve;

use PHPUnit\Framework\TestCase;

class ResolveTest extends TestCase
{
	public function testShift()
	{
		$resolver = new Resolve(
			function () {
			}
		);

		$this->assertEquals([], $resolver->shift(fn () => 24));
		$this->assertEquals([], $resolver->shift('foo'));
		$this->assertEquals(['world'], $resolver->shift('|world'));
		$this->assertEquals(['world'], $resolver->shift('hello|world'));
		$this->assertEquals(['world|', 'universe'], $resolver->shift('hello|world|universe'));
		$this->assertEquals(['world|', 'universe'], $resolver->shift(['hello|', 'world|', 'universe']));
	}

	public function testResolveOriginal()
	{
		$resolver = new Resolve(
			function () {
			}
		);

		$resolutionType = '';
		$this->assertEquals(23, ($resolver->resolve(fn () => 23, $resolutionType))());
		$this->assertEquals(ResolveAdaptorInterface::TYPE_ORIGINAL, $resolutionType);
	}

	public function testResolveInvokable()
	{
		$resolver = new Resolve(
			function ($value) {
				return fn () => $value . 'Invoke';
			}
		);

		$resolutionType = '';
		$this->assertEquals('fooInvoke', ($resolver->resolve('foo', $resolutionType))());
		$this->assertEquals(ResolveAdaptorInterface::TYPE_INVOKE, $resolutionType);
	}

	public function testResolveInstance()
	{
		$resolver = new Resolve(fn ($value) => new $value);

		$resolutionType = '';
		$result = $resolver->resolve(ResolveTestClass::class . '->bar', $resolutionType);
		$this->assertInstanceOf('\Closure', $result);
		$this->assertEquals('bar', $result());
		$this->assertEquals(ResolveAdaptorInterface::TYPE_INSTANCE, $resolutionType);
	}

	public function testResolveStatic()
	{
		$resolver = new Resolve(
			function () {
			}
		);

		$resolutionType = '';
		$result = $resolver->resolve(ResolveTestClass::class . '::foo', $resolutionType);
		$this->assertInstanceOf('\Closure', $result);
		$this->assertEquals('foo', $result());
		$this->assertEquals(ResolveAdaptorInterface::TYPE_STATIC, $resolutionType);
	}

	public function testResolvePipeline()
	{
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$response1 = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response2 = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$response2->method('getReasonPhrase')->willReturn('asdfg');

		$pipelineMock =  $this->createMock(\Weave\Middleware\Middleware::class);
		$pipelineMock->method('chain')
		->with(
			$this->equalTo('foo'),
			$this->equalTo($request),
			$this->equalTo($response1)
		)
		->willReturn($response2);

		$resolver = new Resolve(fn ($value) => $pipelineMock);

		$resolutionType = '';
		$result = $resolver->resolve('foo|', $resolutionType);
		$this->assertInstanceOf('\Closure', $result);
		$this->assertEquals(ResolveAdaptorInterface::TYPE_PIPELINE, $resolutionType);
		$this->assertEquals('asdfg', $result($request, $response1)->getReasonPhrase());
	}
}
