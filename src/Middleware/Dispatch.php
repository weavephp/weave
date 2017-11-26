<?php
declare(strict_types = 1);
/**
 * Weave Core.
 */

namespace Weave\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Weave\Resolve\ResolveAdaptorInterface;
use Weave\Dispatch\DispatchAdaptorInterface;

/**
 * Weave PSR7 middleware Dispatcher.
 *
 * Given a PSR7 Request with a dispatch.handler attribute, process it and dispatch.
 * Supports direct dispatch of callables, resolution of static and instance methods,
 * invokables and middleware pipelines.
 */
class Dispatch
{
	/**
	 * Resolver interface instance.
	 *
	 * @var ResolveAdaptorInterface
	 */
	protected $resolver;

	/**
	 * Dispatcher interface instance.
	 *
	 * @var DispatchAdaptorInterface
	 */
	protected $dispatcher;

	/**
	 * Constructor.
	 *
	 * @param ResolveAdaptorInterface  $resolver   The resolver.
	 * @param DispatchAdaptorInterface $dispatcher The dispatcher.
	 */
	public function __construct(
		ResolveAdaptorInterface $resolver,
		DispatchAdaptorInterface $dispatcher
	) {
		$this->resolver = $resolver;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * PSR7 middleware double-pass entrypoint.
	 *
	 * @param Request  $request  The PSR7 request.
	 * @param mixed    $response Some form of PSR7-style response or a PSR15 delegate.
	 * @param callable $next     Some form of callable to the next pipeline entry.
	 *
	 * @return mixed Some form of PSR7-style Response.
	 */
	public function __invoke(Request $request, $response, $next = null)
	{
		// Cope with invoked single-pass and invoked double-pass middlewares
		if (is_callable($response)) {
			$next = $response;
			$response = null;
		}

		// If there's nothing to dispatch, continue along the middleware pipeline
		$handler = $request->getAttribute('dispatch.handler', false);
		if ($handler === false) {
			return $next($request, $response);
		}

		// Setup any remaining chained parts of the dispatch for future dispatch
		$request = $request->withAttribute('dispatch.handler', $this->resolver->shift($handler));

		// Resolve the handler into something callable
		$dispatchable = $this->resolver->resolve($handler, $resolutionType);

		// Dispatch
		$dispatchResponse = $this->dispatcher->dispatch(
			$dispatchable,
			$resolutionType,
			DispatchAdaptorInterface::SOURCE_DISPATCH_MIDDLEWARE,
			$request,
			$response
		);

		// If something was returned then return back down the middleware
		if ($dispatchResponse !== false) {
			return $dispatchResponse;
		}

		// Otherwise keep going up the middleware pipeline
		return $next($request, $response);
	}

	/**
	 * PSR7 middleware PSR15 draft single-pass entrypoint.
	 *
	 * @param Request $request The PSR7 request.
	 * @param mixed   $next    Some form of delegate to the next pipeline entry.
	 *
	 * @return mixed Some form of PSR7-style Response.
	 */
	public function process(Request $request, $next)
	{
		// If there's nothing to dispatch, continue along the middleware pipeline
		$handler = $request->getAttribute('dispatch.handler', false);
		if ($handler === false) {
			return $next($request);
		}

		// Setup any remaining chained parts of the dispatch for future dispatch
		$request = $request->withAttribute('dispatch.handler', $this->resolver->shift($handler));

		// Resolve the handler into something callable
		$dispatchable = $this->resolver->resolve($handler, $resolutionType);

		// Dispatch
		$dispatchResponse = $this->dispatcher->dispatch(
			$dispatchable,
			$resolutionType,
			DispatchAdaptorInterface::SOURCE_DISPATCH_MIDDLEWARE,
			$request
		);

		// If something was returned then return back down the middleware
		if ($dispatchResponse !== false) {
			return $dispatchResponse;
		}

		// Otherwise keep going up the middleware pipeline
		if (method_exists($next, 'handle')) {
			return $next->handle($request);
		} else {
			return $next->process($request);
		}
	}
}
