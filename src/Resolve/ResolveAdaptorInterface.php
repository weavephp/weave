<?php
declare(strict_types = 1);
/**
 * Weave Core.
 */
namespace Weave\Resolve;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Weave core resolver used by the Router and Dispatcher classes.
 */
interface ResolveAdaptorInterface
{
	/**
	 * Attempt to convert a provided value into a callable.
	 *
	 * If the value isn't a string it is simply returned.
	 * If the string value contains '|' treat it as a pipeline name.
	 * If the string value contains '::' treat it as a static method call.
	 * If the string value contains '->' treat it as an instance method call.
	 * Otherwise, attempt to treat it as an invokable.
	 *
	 * @param mixed $value The value to resolve. Usually a string or callable.
	 *
	 * @return mixed Usually some form of callable.
	 */
	public function resolve($value);
}
