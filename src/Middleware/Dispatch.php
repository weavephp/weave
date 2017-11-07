<?php
declare(strict_types = 1);
/**
 * Weave Core.
 */

namespace Weave\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Weave\Resolve\ResolveAdaptorInterface;

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
	 * Constructor.
	 *
	 * @param ResolveAdaptorInterface $resolver The resolver.
	 */
	public function __construct(ResolveAdaptorInterface $resolver)
	{
		$this->resolver = $resolver;
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
		if (is_callable($response)) {
			$next = $response;
			$response = null;
		}

		$handler = $request->getAttribute('dispatch.handler', false);
		if ($handler === false) {
			return $next($request, $response);
		}

		$request = $request->withAttribute('dispatch.handler', $this->resolver->shift($handler));

		$dispatchable = $this->resolver->resolve($handler);
		$dispatchResponse = $dispatchable($request, $response);

		if ($dispatchResponse !== false) {
			return $dispatchResponse;
		}

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
		$handler = $request->getAttribute('dispatch.handler');

		$dispatchable = $this->resolver->resolve($handler);
		$dispatchResponse = $dispatchable($request, $response);

		if ($dispatchResponse !== false) {
			return $dispatchResponse;
		}

		if (method_exists($next, 'handle')) {
			return $next->handle($request);
		} else {
			return $next->process($request);
		}
	}
}
