<?php
declare(strict_types = 1);
/**
 * Weave Core.
 */
namespace Weave\Resolve;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Weave core resolver methods used by the Router and Dispatcher classes.
 */
trait Resolve
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
	protected function _resolve($value)
	{
		if (is_string($value)) {
			if (strpos($value, '|') !== false) {
				return $this->_resolveMiddlewarePipeline($value);
			} elseif (strpos($value, '::') !== false) {
				return $this->_resolveStatic($value);
			} elseif (strpos($value, '->') !== false) {
				return $this->_resolveInstanceMethod($value);
			} else {
				return $this->_resolveInvokable($value);
			}
		} else {
			return $value;
		}
	}

	/**
	 * Resolve the provided value to a named middleware chain.
	 *
	 * @param string $value The name of the middleware chain plus optional chained dispatch.
	 *
	 * @return callable A callable that executes the middleware chain.
	 */
	protected function _resolveMiddlewarePipeline($value)
	{
		$value = strstr($value, '|', true);
		$middleware = ($this->_resolver)(\Weave\Middleware\Middleware::class);
		return function (Request $request, $response = null) use ($middleware, $value) {
			return $middleware->chain($value, $request, $response);
		};
	}

	/**
	 * Resolve the provided value to a static class method call.
	 *
	 * @param string $value The class and static method definition string.
	 *
	 * @return callable A callable that executes the static method.
	 */
	protected function _resolveStatic($value)
	{
		return \Closure::fromCallable($value);
	}

	/**
	 * Resolve the provided value to an instancelass method call.
	 *
	 * @param string $value The class and method definition string.
	 *
	 * @return callable A callable that executes the instance method.
	 */
	protected function _resolveInstanceMethod($value)
	{
		$callable = explode('->', $value);
		$callable[0] = ($this->_resolver)($callable[0]);
		return \Closure::fromCallable($callable);
	}

	/**
	 * Resolve the provided value to an invokable.
	 *
	 * @param string $value The class name string.
	 *
	 * @return callable The invokable class instance.
	 */
	protected function _resolveInvokable($value)
	{
		return ($this->_resolver)($value);
	}
}