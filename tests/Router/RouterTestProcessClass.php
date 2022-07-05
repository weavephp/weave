<?php

declare(strict_types = 1);

/**
 * Weave Core.
 */
namespace Weave\Router;

class RouterTestProcessClass
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
