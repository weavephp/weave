<?php
declare(strict_types = 1);
/**
 * Weave Core.
 */
namespace Weave\Router;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * The Weave Router.
 *
 * This class acts as a Middleware and wraps the chosen Router Adaptor.
 */
class Router
{
	use \Weave\Resolve\Resolve;

	/**
	 * The Router adaptor instance.
	 *
	 * @var RouterAdaptorInterface
	 */
	protected $_adaptor;

	/**
	 * The route provider callable.
	 *
	 * @var callable
	 */
	protected $_routeProvider;

	/**
	 * The resolver callable.
	 *
	 * @var callable
	 */
	protected $_resolver;

	/**
	 * Constructor.
	 *
	 * @param RouterAdaptorInterface $adaptor       The Router Adaptor.
	 * @param callable               $routeProvider The route provider callable.
	 * @param callable               $resolver      The resolver callable.

	 */
	public function __construct(
		RouterAdaptorInterface $adaptor,
		callable $routeProvider,
		callable $resolver
	) {
		$this->_adaptor = $adaptor;
		$this->_routeProvider = $routeProvider;
		$this->_resolver = $resolver;
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
		$routeResponse = $this->_route($request, $response);

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
		$routeResponse = $this->_route($request);

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
	protected function _route(Request $request, $response = null)
	{
		$this->_adaptor->configureRoutes($this->_routeProvider);
		$routeDetails = $this->_adaptor->route($request);

		if ($routeDetails === false) {
			return false;
		}

		list($request, $handler) = $routeDetails;

		if (is_string($handler) && strpos($handler, '|') !== false) {
			$secondaryHandler = substr($handler, strpos($handler, '|') + 1);
			$request = $request->withAttribute('dispatch.handler', $secondaryHandler);
		}

		$dispatchable = $this->_resolve($handler);
		return $dispatchable($request, $response);
	}
}