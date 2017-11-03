<?php
declare(strict_types = 1);
/**
 * Weave core.
 */

namespace Weave\Config;

/**
 * Weave null config adaptor.
 *
 * This is just a convenience for when you don't want a config array.
 */
trait None
{
	/**
	 * Simply return an empty array.
	 *
	 * @param string $environment    Optional indication of the runtime environment.
	 * @param string $configLocation Optional location from which to load config.
	 *
	 * @return array
	 */
	protected function _loadConfig($environment = null, $configLocation = null)
	{
		return [];
	}
}