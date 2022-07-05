<?php

declare(strict_types = 1);

/**
 * Weave Core.
 */
namespace Weave\Config;

use PHPUnit\Framework\TestCase;

class NoneTest extends TestCase
{
	public function testMethodExists()
	{
		$this->assertTrue(method_exists(None::class, 'loadConfig'));
	}

	public function testMethodBehaviour()
	{
		$testClassInstance = new NoneTestClass();
		$this->assertEquals([], $testClassInstance->loadConfig());
	}
}
