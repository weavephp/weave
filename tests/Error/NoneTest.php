<?php
/**
 * Weave Core.
 */
namespace Weave\Error;

use PHPUnit\Framework\TestCase;

class NoneTest extends TestCase
{
	public function testMethodExists()
	{
		$this->assertTrue(method_exists(None::class, 'loadErrorHandler'));
	}

	public function testMethodBehaviour()
	{
		$testClassInstance = new NoneTestClass();
		$this->assertEquals(null, $testClassInstance->loadErrorHandler());
	}
}
