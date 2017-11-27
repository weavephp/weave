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
class Router
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
		$routeResponse = $this->route($request, $response);

		if ($routeResponse !== false) {
			return $routeResponse;
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
		$routeResponse = $this->route($request);

		if ($routeResponse !== false) {
			return $routeResponse;
		}

		if (method_exists($next, 'handle')) {
			return $next->handle($request);
		} else {
			return $next->process($request);
		}
	}

	/**
	 * Configure and then attempt to route and dispatch for the supplied Request.
	 *
	 * @param Request $request The request to route.
	 * @param Response $response The response (for double-pass middleware).
	 *
	 * @return Response|false Returns false if unable to route.
	 */
	protected function route(Request $request, $response = null)
	{
		if (!$this->routesConfigured) {
			$this->adaptor->configureRoutes($this->routeProvider);
			$this->routesConfigured = true;
		}

		$handler = $this->adaptor->route($request);

		if ($handler === false) {
			return false;
		}

		$request = $request->withAttribute('dispatch.handler', $this->resolver->shift($handler));

		$dispatchable = $this->resolver->resolve($handler, $resolutionType);
		return $this->dispatcher->dispatch(
			$dispatchable,
			$resolutionType,
			DispatchAdaptorInterface::SOURCE_ROUTER,
			$request,
			$response
		);
	}
}
