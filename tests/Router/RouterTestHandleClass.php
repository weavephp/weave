<?php

declare(strict_types = 1);

/**
 * Weave Core.
 */
namespace Weave\Router;

class RouterTestHandleClass
{
	public $response;

	public function __construct($response)
	{
		$this->response = $response;
	}

	public function handle()
	{
		return $this->response;
	}
}
