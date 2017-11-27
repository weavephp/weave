<?php
declare(strict_types = 1);
/**
 * Weave Core.
 */
namespace Weave\Error;

class NoneTestClass
{
	use None {
		loadErrorHandler as public;
	}
}
