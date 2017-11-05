<?php
declare(strict_types = 1);
/**
 * Weave core.
 */

namespace Weave\Error;

/**
 * Weave null error handler adaptor.
 *
 * This is just a convenience for when you don't want an error handler.
 */
trait None
{
	/**
	 * Do nothing.
	 *
	 * @param array  $config      Optional config array as provided from loadConfig().
	 * @param string $environment Optional indication of the runtime environment.
	 *
	 * @return null
	 */
	protected function loadErrorHandler(array $config = [], $environment = null)
	{
	}
}
