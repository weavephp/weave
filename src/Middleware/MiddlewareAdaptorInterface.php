<?php

declare(strict_types = 1);

/**
 * Weave Core.
 */
namespace Weave\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Weave Middleware Adaptor interface.
 *
 * Each supported middleware package should have an adaptor that adheres to this interface.
 */
interface MiddlewareAdaptorInterface
{
	/**
	 * Set a callable on the Adaptor that can be used to resolve class strings to class instances.
	 *
	 * @param callable $resolver The resolver.
	 *
	 * @return void
	 */
	public function setResolver(callable $resolver): void;

	/**
	 * Whether the Middleware the Adaptor wraps is a single- or double-pass style Middleware stack.
	 *
	 * @return boolean
	 */
	public function isDoublePass(): bool;

	/**
	 * Trigger execution of the supplied pipeline via the Middleware stack.
	 *
	 * @param mixed     $pipeline The stack of middleware definitions.
	 * @param Request   $request  The PSR7 request.
	 * @param ?Response $response The PSR7 response (for double-pass stacks).
	 *
	 * @return Response
	 */
	public function executePipeline(mixed $pipeline, Request $request, ?Response $response = null): Response;
}
