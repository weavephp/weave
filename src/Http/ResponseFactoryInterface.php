<?php
/**
 * Weave Core.
 */

namespace Weave\Http;

use Psr\Http\Message\ResponseInterface as Response;

/**
 * Weave HTTP / PSR7 Response object factory interface.
 */
interface ResponseFactoryInterface
{
	/**
	 * Generate and return a fresh PSR7 Response instance.
	 *
	 * @return Response
	 */
	public function newResponse();
}
