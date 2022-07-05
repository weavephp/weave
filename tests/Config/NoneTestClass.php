<?php

declare(strict_types = 1);

/**
 * Weave Core.
 */
namespace Weave\Config;

class NoneTestClass
{
	use None {
		loadConfig as public;
	}
}
