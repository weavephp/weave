<?php
declare(strict_types = 1);
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
	 * The method should return an array with 2 values. The first is a Request
	 * object with any additional attributes applied. The second is something
	 * dispatchable - a callable or a string that can be resolved to a callable.
	 *
	 * If the Request can't be routed, return false.
	 *
	 * @param Request $request The PSR7 request to attempt to route.
	 *
	 * @return false|array[Request, string|callable]
	 */
	public function route(Request $request);
}