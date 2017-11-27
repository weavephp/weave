<?php
/**
 * Weave core.
 */

namespace Weave\Http;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Weave HTTP / PSR7 Request object factory interface.
 */
interface RequestFactoryInterface
{
	/**
	 * Create and return a PSR7 ServerRequestInterface complient object instance from global data.
	 *
	 * @return Request A new Request object based on global data.
	 */
	public function newIncomingRequest();
}
