<?php
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
		$config = $this->loadConfig($environment, $configLocation);
		$this->loadErrorHandler($config, $environment);
		$instantiator = $this->loadContainer($config, $environment);

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
	abstract protected function loadConfig($environment = null, $configLocation = null);

	/**
	 * Setup a global error handler.
	 *
	 * @param array  $config      Optional config array as provided from loadConfig().
	 * @param string $environment Optional indication of the runtime environment.
	 *
	 * @return null
	 */
	abstract protected function loadErrorHandler(array $config = [], $environment = null);

	/**
	 * Setup the Dependency Injection Container.
	 *
	 * @param array  $config      Optional config array as provided from loadConfig().
	 * @param string $environment Optional indication of the runtime environment.
	 *
	 * @return callable A callable that can instantiate instances of classes from the DIC.
	 */
	abstract protected function loadContainer(array $config = [], $environment = null);
}
