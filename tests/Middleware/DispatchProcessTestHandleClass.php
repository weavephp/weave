<?php

declare(strict_types = 1);

/**
 * Weave Core.
 */
namespace Weave\Middleware;

class DispatchProcessTestHandleClass
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
