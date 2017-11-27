<?php
/**
 * Weave Core.
 */
namespace Weave\Dispatch;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Weave core dispatcher used by the Router and Dispatcher Middleware classes.
 */
interface DispatchAdaptorInterface
{
	/**
	 * Consts representing where the dispatch was called from.
	 */
	const SOURCE_ROUTER = 'router';
	const SOURCE_MIDDLEWARE_STACK = 'middlewareStack';
	const SOURCE_DISPATCH_MIDDLEWARE = 'dispatchMiddleware';

	/**
	 * Call the callable, providing parameters and returning the returned value.
	 *
	 * $resolutionType and $dispatchSource can help in identifying when to conditionally
	 * wrap the dispatch to handle translation, templating etc.
	 *
	 * @param callable $dispatchable   The callable to be called.
	 * @param string   $resolutionType Are we dispatching to a static, invokable etc.
	 * @param string   $dispatchSource Where the dispatch request came from.
	 * @param Request  $request        The request.
	 * @param mixed    ...$rest        Any remaining parameters passed to the callable.
	 *
	 * @return mixed Some form of PSR-7 style response.
	 */
	public function dispatch($dispatchable, $resolutionType, $dispatchSource, Request $request, ...$rest);
}
