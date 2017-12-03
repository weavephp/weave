<?php
/**
 * Weave Core.
 */
namespace Weave;

use PHPUnit\Framework\TestCase;

class WeaveTest extends TestCase
{
	public function testStart()
	{
		$response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);

		$middleware = $this->createMock(Middleware\Middleware::class);
		$middleware->expects($this->once())
		->method('execute')
		->willReturn($response);

		$middleware->expects($this->once())
		->method('emit')
		->with($this->equalTo($response));

		$weaveInstance = $this->getMockBuilder(WeaveTestClass::class)
		->setMethods(['loadConfig', 'loadErrorHandler', 'loadContainer'])
		->getMock();

		$weaveInstance->expects($this->once())
		->method('loadConfig')
		->with(
			$this->equalTo('foo'),
			$this->equalTo('baa')
		)
		->willReturn(['ping']);

		$weaveInstance->expects($this->once())
		->method('loadErrorHandler')
		->with(
			$this->equalTo(['ping']),
			$this->equalTo('foo')
		);

		$weaveInstance->expects($this->once())
		->method('loadContainer')
		->with(
			$this->equalTo(['ping']),
			$this->equalTo('foo')
		)
		->willReturn(
			function () use ($middleware) {
				return $middleware;
			}
		);

		$result = $weaveInstance->start('foo', 'baa');
	}

	/*
		public function start($environment = null, $configLocation = null)
	{
		$config = $this->loadConfig($environment, $configLocation);
		$this->loadErrorHandler($config, $environment);
		$instantiator = $this->loadContainer($config, $environment);

		$middleware = $instantiator(Middleware\Middleware::class);
		$response = $middleware->execute();

		$middleware->emit($response);
	}
	*/
}
