<?php
/**
 * Weave Core.
 */
namespace Weave\Dispatch;

use PHPUnit\Framework\TestCase;

class DispatchTest extends TestCase
{
	public function testDispatch()
	{
		$dispatch = new Dispatch();
		$dispatchable = function ($request, ...$rest) {
			return [$request, $rest];
		};
		$request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
		$additionalParameters = ['foo', 'bar'];

		$result = $dispatch->dispatch(
			$dispatchable,
			\Weave\Resolve\ResolveAdaptorInterface::TYPE_ORIGINAL,
			DispatchAdaptorInterface::SOURCE_ROUTER,
			$request,
			...$additionalParameters
		);
		$this->assertEquals([$request, $additionalParameters], $result);
	}
}
