<?php
declare(strict_types = 1);
/**
 * Weave core.
 */

namespace Weave;

/**
 * Weave Core.
 */
trait Weave
{
	/**
	 * The starting point for any Weave App.
	 *
	 * Environment and config location details are passed on to the config adaptor
	 * and environment is also passed to the error adaptor and the container adaptor.
	 *
	 * @param string $environment    Optional indication of the runtime environment.
	 * @param string $configLocation Optional location from which to load config.
	 *
	 * @return null
	 */
	public function start($environment = null, $configLocation = null)
	{
		$config = $this->_loadConfig($environment, $configLocation);
		$this->_loadErrorHandler($config, $environment);
		$instantiator = $this->_loadContainer($config, $environment);

		$middleware = $instantiator(Middleware\Middleware::class);
		$response = $middleware->execute();

		$middleware->emit($response);
	}

	/**
	 * Load config and return as an array.
	 *
	 * @param string $environment    Optional indication of the runtime environment.
	 * @param string $configLocation Optional location from which to load config.
	 *
	 * @return array
	 */
	abstract protected function _loadConfig($environment = null, $configLocation = null);

	/**
	 * Setup a global error handler.
	 *
	 * @param array  $config      Optional config array as provided from _loadConfig().
	 * @param string $environment Optional indication of the runtime environment.
	 *
	 * @return null
	 */
	abstract protected function _loadErrorHandler(array $config = [], $environment = null);

	/**
	 * Setup the Dependency Injection Container.
	 *
	 * @param array  $config      Optional config array as provided from _loadConfig().
	 * @param string $environment Optional indication of the runtime environment.
	 *
	 * @return callable A callable that can instantiate instances of classes from the DIC.
	 */
	abstract protected function _loadContainer(array $config = [], $environment = null);

	/**
	 * Provide middleware pipeline sets.
	 *
	 * A Pipeline is a stack (usually array) of callables or strings that can be resolved to callables.
	 * The default pipeline and the only one required to be supported has a pipeline name of null.
	 *
	 * @param string $pipelineName The name of the pipeline to return a definition for.
	 *
	 * @return mixed Whatever the chosen Middleware stack uses for a pipeline of middlewares.
	 */
	abstract protected function _provideMiddlewarePipeline($pipelineName = null);

	/**
	 * Setup routes for the Router.
	 *
	 * How routes are setup is Router-specific so see the Router docs for details.
	 *
	 * @param mixed $router The object to setup routes against - router specific.
	 *
	 * @return null
	 */
	abstract protected function _provideRouteConfiguration($router);
}
