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
class Resolve implements ResolveAdaptorInterface
{
	/**
	 * The class instance instantiator callable.
	 *
	 * @var callable
	 */
	protected $instantiator;

	/**
	 * Constructor.
	 *
	 * @param callable $instantiator The instantiator.
	 */
	public function __construct(callable $instantiator)
	{
		$this->instantiator = $instantiator;
	}

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
	public function resolve($value)
	{
		if (is_string($value)) {
			if (strpos($value, '|') !== false) {
				return $this->resolveMiddlewarePipeline($value);
			} elseif (strpos($value, '::') !== false) {
				return $this->resolveStatic($value);
			} elseif (strpos($value, '->') !== false) {
				return $this->resolveInstanceMethod($value);
			} else {
				return $this->resolveInvokable($value);
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
	protected function resolveMiddlewarePipeline($value)
	{
		$value = strstr($value, '|', true);
		$middleware = ($this->instantiator)(\Weave\Middleware\Middleware::class);
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
	protected function resolveStatic($value)
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
	protected function resolveInstanceMethod($value)
	{
		$callable = explode('->', $value);
		$callable[0] = ($this->instantiator)($callable[0]);
		return \Closure::fromCallable($callable);
	}

	/**
	 * Resolve the provided value to an invokable.
	 *
	 * @param string $value The class name string.
	 *
	 * @return callable The invokable class instance.
	 */
	protected function resolveInvokable($value)
	{
		return ($this->instantiator)($value);
	}
}
