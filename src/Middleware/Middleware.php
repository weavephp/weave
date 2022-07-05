<?php

declare(strict_types = 1);

/**
 * Weave Core.
 */

namespace Weave\Middleware;

use Weave\Http;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Weave\Resolve\ResolveAdaptorInterface;
use Weave\Dispatch\DispatchAdaptorInterface;

/**
 * Middleware Pipeline adaptor manager.
 */
class Middleware
{
	/**
	 * The pipeline provider callable.
	 *
	 * @var callable
	 */
	protected $pipelineProvider;

	/**
	 * Constructor.
	 *
	 * @param MiddlewareAdaptorInterface    $adaptor          The middleware adaptor.
	 * @param callable                      $pipelineProvider The pipeline provider.
	 * @param ResolveAdaptorInterface       $resolver         The resolver.
	 * @param DispatchAdaptorInterface      $dispatcher       The dispatcher.
	 * @param Http\RequestFactoryInterface  $requestFactory   The PSR7 Request factory.
	 * @param Http\ResponseFactoryInterface $responseFactory  The PSR7 Response factory.
	 * @param Http\ResponseEmitterInterface $emitter          The PSR7 Response emitter.
	 */
	public function __construct(
		protected MiddlewareAdaptorInterface $adaptor,
		callable $pipelineProvider,
		ResolveAdaptorInterface $resolver,
		DispatchAdaptorInterface $dispatcher,
		protected Http\RequestFactoryInterface $requestFactory,
		protected Http\ResponseFactoryInterface $responseFactory,
		protected Http\ResponseEmitterInterface $emitter
	) {
		$this->pipelineProvider = $pipelineProvider;
		$this->adaptor->setResolver(
			function ($value) use ($resolver, $dispatcher) {
				$resolutionType = ''; // overwritten by the resolve call below
				$dispatchable = $resolver->resolve($value, $resolutionType);
				return function (Request $request, ...$rest) use ($dispatchable, $dispatcher, $resolutionType) {
					return $dispatcher->dispatch(
						$dispatchable,
						$resolutionType,
						DispatchAdaptorInterface::SOURCE_MIDDLEWARE_STACK,
						$request,
						...$rest
					);
				};
			}
		);
	}

	/**
	 * Create and execute the named pipeline as an initial entry pipeline.
	 *
	 * Similar to chaining but also sets up a Request based on the global data and,
	 * if needed, a Response object.
	 *
	 * @param ?string $pipelineName Optional pipeline name.
	 *
	 * @return Response A PSR7 response object.
	 */
	public function execute(?string $pipelineName = null): Response
	{
		$request = $this->requestFactory->newIncomingRequest();

		return $this->chain(
			$pipelineName,
			$request,
			$this->adaptor->isDoublePass() ? $this->responseFactory->newResponse() : null
		);
	}

	/**
	 * Create and execute a named pipeline chaining from an existing Request/Response.
	 *
	 * @param ?string    $pipelineName The name of the pipeline to create.
	 * @param Request   $request      The PSR7 request object.
	 * @param ?Response $response     The PSR7 response object (for double-pass pipelines).
	 *
	 * @return Response A PSR7 response.
	 */
	public function chain(?string $pipelineName, Request $request, ?Response $response = null): Response
	{
		$pipelineProvider = $this->pipelineProvider;
		$pipeline = $pipelineProvider($pipelineName);
		return $this->adaptor->executePipeline($pipeline, $request, $response);
	}

	/**
	 * Output a PSR7 Response to the client.
	 *
	 * @param Response $response The PSR7 response to output to the client.
	 *
	 * @return void
	 */
	public function emit(Response $response): void
	{
		$this->emitter->emit($response);
	}
}
