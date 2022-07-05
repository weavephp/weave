<?php

declare(strict_types = 1);

/**
 * Weave Core.
 */
namespace Weave\Middleware;

class DispatchProcessTestProcessClass
{
	public $response;

	public function __construct($response)
	{
		$this->response = $response;
	}

	public function process()
	{
		return $this->response;
	}
}
