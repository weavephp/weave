<?php
/**
 * Weave Core.
 */
namespace Weave\Router;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Weave Router Adaptor Interface.
 */
interface RouterAdaptorInterface
{
	/**
	 * Using the provided callable, configure the Router's routes.
	 *
	 * @param callable $routeProvider The method to use to configure the routes.
	 *
	 * @return null
	 */
	public function configureRoutes(callable $routeProvider);

	/**
	 * Route the supplied request.
	 *
	 * If the Request can't be routed, return false.
	 *
	 * @param Request &$request The PSR7 request to attempt to route.
	 *
	 * @return false|string|callable
	 */
	public function route(Request &$request);
}
