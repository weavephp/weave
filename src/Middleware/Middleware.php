<?php
declare(strict_types = 1);
/**
 * Weave Core.
 */

namespace Weave\Middleware;

use Weave\Http;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Middleware Pipeline adaptor manager.
 */
class Middleware
{
	/**
	 * The Middleware adaptor.
	 *
	 * @var MiddlewareAdaptorInterface
	 */
	protected $adaptor;

	/**
	 * The pipeline provider callable.
	 *
	 * @var callable
	 */
	protected $pipelineProvider;

	/**
	 * The class instance resolver callable.
	 *
	 * @var callable
	 */
	protected $resolver;

	/**
	 * The PSR7 request object factory.
	 *
	 * @var Http\RequestFactoryInterface
	 */
	protected $requestFactory;

	/**
	 * The PSR7 response object factory.
	 *
	 * @var Http\ResponseFactoryInterface
	 */
	protected $responseFactory;

	/**
	 * The PSR7 response emitter.
	 *
	 * @var Http\ResponseEmitterInterface
	 */
	protected $emitter;

	/**
	 * Constructor.
	 *
	 * @param MiddlewareAdaptorInterface    $adaptor          The middleware adaptor.
	 * @param callable                      $pipelineProvider The pipeline provider.
	 * @param callable                      $resolver         The resolver.
	 * @param Http\RequestFactoryInterface  $requestFactory   The PSR7 Request factory.
	 * @param Http\ResponseFactoryInterface $responseFactory  The PSR7 Response factory.
	 * @param Http\ResponseEmitterInterface $emitter          The PSR7 Response emitter.
	 */
	public function __construct(
		MiddlewareAdaptorInterface $adaptor,
		callable $pipelineProvider,
		callable $resolver,
		Http\RequestFactoryInterface $requestFactory,
		Http\ResponseFactoryInterface $responseFactory,
		Http\ResponseEmitterInterface $emitter
	) {
		$this->adaptor = $adaptor;
		$this->pipelineProvider = $pipelineProvider;
		$this->resolver = $resolver;
		$this->requestFactory = $requestFactory;
		$this->responseFactory = $responseFactory;
		$this->emitter = $emitter;

		$this->adaptor->setResolver(
			function ($value) {
				return $this->resolve($value);
			}
		);
	}

	/**
	 * Create and execute the named pipeline as an initial entry pipeline.
	 *
	 * Similar to chaining but also sets up a Request based on the global data and,
	 * if needed, a Response object.
	 *
	 * @param string $pipelineName Optional pipeline name.
	 *
	 * @return Response A PSR7 response object.
	 */
	public function execute($pipelineName = null)
	{
		$request = $this->requestFactory->newIncomingRequest();
		if ($this->adaptor->isDoublePass()) {
			$response = $this->responseFactory->newResponse();
		} else {
			$response = null;
		}
		return $this->chain($pipelineName, $request, $response);
	}

	/**
	 * Create and execute a named pipeline chaining from an existing Request/Response.
	 *
	 * @param string        $pipelineName The name of the pipeline to create.
	 * @param Request       $request      The PSR7 request object.
	 * @param Response|null $response     The PSR7 response object (for double-pass pipelines).
	 *
	 * @return Response A PSR7 response.
	 */
	public function chain($pipelineName, Request $request, Response $response = null)
	{
		$pipeline = ($this->pipelineProvider)($pipelineName);
		return $this->adaptor->executePipeline($pipeline, $request, $response);
	}

	/**
	 * Output a PSR7 Response to the client.
	 *
	 * @param Response The PSR7 response to output to the client.
	 *
	 * @return null
	 */
	public function emit(Response $response)
	{
		$this->emitter->emit($response);
	}

	/**
	 * Resolve a class string to a callable.
	 *
	 * If the value provided is a string and can be resolved to a class instance then
	 * the class instance is resolved and returned. The class instance should behave
	 * as to match the type of pipeline adaptor in use - single- or double-pass.
	 *
	 * If the value is provided isn't a string it is simply returned (so you can
	 * provide closures and existing class instances in a pipeline definition).
	 *
	 * @param mixed $value The value that might need resolving.
	 *
	 * @return mixed
	 */
	protected function resolve($value)
	{
		if (is_string($value)) {
			return ($this->resolver)($value);
		} else {
			return $value;
		}
	}
}
