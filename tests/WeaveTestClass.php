<?php

declare(strict_types = 1);

/**
 * Weave Core.
 */
namespace Weave;

class WeaveTestClass
{
	use Weave;

	protected function loadConfig(?string $environment = null, ?string $configLocation = null): array
	{
	}

	protected function loadErrorHandler(array $config = [], ?string $environment = null)
	{
	}

	protected function loadContainer(array $config = [], ?string $environment = null)
	{
	}
}
