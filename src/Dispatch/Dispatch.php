<?php
/**
 * Weave Core.
 */
namespace Weave\Dispatch;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Weave core dispatcher used by the Router and Dispatcher Middleware classes.
 */
class Dispatch implements DispatchAdaptorInterface
{
	/**
	 * Call the callable, providing parameters and returning the returned value.
	 *
	 * Override this method in subclasses to easily wrap the actual dispatch callable
	 * and provide pre- and post-dispatch custom functionality.
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
	public function dispatch($dispatchable, $resolutionType, $dispatchSource, Request $request, ...$rest)
	{
		return $dispatchable($request, ...$rest);
	}
}
