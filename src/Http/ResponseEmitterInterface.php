<?php
declare(strict_types = 1);
/**
 * Weave core.
 */

namespace Weave\Http;

use Psr\Http\Message\ResponseInterface as Response;

/**
 * Weave HTTP / PSR7 Response Emitter interface.
 */
interface ResponseEmitterInterface
{
	/**
	 * Output to the client the contents of the provided PSR7 Response object.
	 *
	 * @param Response $response The response object to emit to the client.
	 *
	 * @return null
	 */
	public function emit(Response $response);
}
