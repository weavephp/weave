<?php
/**
 * Weave Core.
 */
namespace Weave\Router;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Weave\Resolve\ResolveAdaptorInterface;
use Weave\Dispatch\DispatchAdaptorInterface;

/**
 * The Weave Router.
 *
 * This class acts as a Middleware and wraps the chosen Router Adaptor.
 */
class Router extends \Weave\Adaptor\Middleware\Base
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
	 * The Router adaptor instance.
	 *
	 * @var RouterAdaptorInterface
	 */
	protected $adaptor;

	/**
	 * The route provider callable.
	 *
	 * @var callable
	 */
	protected $routeProvider;

	/**
	 * Whether the routes have been configured.
	 *
	 * @var boolean
	 */
	protected $routesConfigured = false;

	/**
	 * Constructor.
	 *
	 * @param RouterAdaptorInterface   $adaptor       The Router Adaptor.
	 * @param callable                 $routeProvider The route provider callable.
	 * @param ResolveAdaptorInterface  $resolver      The resolver.
	 * @param DispatchAdaptorInterface $dispatcher    The dispatcher.

	 */
	public function __construct(
		RouterAdaptorInterface $adaptor,
		callable $routeProvider,
		ResolveAdaptorInterface $resolver,
		DispatchAdaptorInterface $dispatcher
	) {
		$this->adaptor = $adaptor;
		$this->routeProvider = $routeProvider;
		$this->resolver = $resolver;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Configure and then attempt to route and dispatch for the supplied Request.
	 *
	 * @param Request $request The request to route.
	 *
	 * @return Response
	 */
	protected function run(Request $request)
	{
		if (!$this->routesConfigured) {
			$this->adaptor->configureRoutes($this->routeProvider);
			$this->routesConfigured = true;
		}

		$handler = $this->adaptor->route($request);

		if ($handler === false) {
			return $this->chain($request);
		}

		// Setup any remaining chained parts of the dispatch for future dispatch
		$request = $request->withAttribute('dispatch.handler', $this->resolver->shift($handler));

		// Resolve the handler into something callable
		$dispatchable = $this->resolver->resolve($handler, $resolutionType);

		$parameters = [
			$dispatchable,
			$resolutionType,
			DispatchAdaptorInterface::SOURCE_ROUTER,
			$request
		];
		$response = $this->getResponseObject();
		if ($response !== null) {
			$parameters[] = $response;
		}
		return $this->dispatcher->dispatch(...$parameters) ?: $this->chain($request);
	}
}
