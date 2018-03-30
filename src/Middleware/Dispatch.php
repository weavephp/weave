<?php
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
class Dispatch extends \Weave\Adaptor\Middleware\Base
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
	 * Handle a dispatch.
	 *
	 * @param Request $request The request.
	 *
	 * @return Response
	 */
	protected function run(Request $request)
	{
		// If there's nothing to dispatch, continue along the middleware pipeline
		$handler = $request->getAttribute('dispatch.handler', false);
		if ($handler === false) {
			return $this->chain($request);
		}

		// Setup any remaining chained parts of the dispatch for future dispatch
		$request = $request->withAttribute('dispatch.handler', $this->resolver->shift($handler));

		// Resolve the handler into something callable
		$dispatchable = $this->resolver->resolve($handler, $resolutionType);

		// Dispatch
		$parameters = [
			$dispatchable,
			$resolutionType,
			DispatchAdaptorInterface::SOURCE_DISPATCH_MIDDLEWARE,
			$request
		];
		$response = $this->getResponseObject();
		if ($response !== null) {
			$parameters[] = $response;
		}
		return $this->dispatcher->dispatch(...$parameters) ?: $this->chain($request);
	}
}
