<?php
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
