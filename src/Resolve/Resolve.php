<?php
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
	 * Return the array of remaining dispatch steps with the first step removed (shifted).
	 *
	 * A dispatch array can contain multiple middleware pipe names separated by a '|' char
	 * which can be progressively consumed by Dispatch middlewares. This method removes a
	 * single dispatch pipeline step, returning the remaining array.
	 *
	 * If a string is passed into the array, attempt to split on the '|' char and return
	 * an array of the remaining steps.
	 *
	 * If something else is passed in then return an empty array.
	 *
	 * @param array|string|callable $values The dispatch steps.
	 *
	 * @return array The array of remaining dispatch steps.
	 */
	public function shift($values)
	{
		if (!is_array($values)) {
			if (!is_string($values)) {
				// Handle callables being passed in.
				return [];
			}

			// Handle a string being passed in
			$components = explode('|', $values);
			if (count($components) < 2) {
				return [];
			} elseif (count($components) === 2) {
				return [$components[1]];
			} else {
				array_shift($components);
				$lastItem = array_pop($components);
				$components = array_map(
					function ($value) {
						return $value . '|';
					},
					$components
				);
				$components[] = $lastItem;
				return $components;
			}
		} else {
			$shiftedArray = $values;
			array_shift($shiftedArray);
			return $shiftedArray;
		}
	}

	/**
	 * Attempt to convert a provided value into a callable.
	 *
	 * If the value is an array, the rest of the rules apply to the first item.
	 * If the value isn't a string it is simply returned.
	 * If the string value contains '|' treat it as a pipeline name.
	 * If the string value contains '::' treat it as a static method call.
	 * If the string value contains '->' treat it as an instance method call.
	 * Otherwise, attempt to treat it as an invokable.
	 *
	 * @param string|callable|array $value           The value to resolve.
	 * @param string                $resolutionType Set to the type of resolution identified.
	 *
	 * @return callable Usually some form of callable.
	 */
	public function resolve($value, &$resolutionType)
	{
		if (is_array($value)) {
			$value = $value[0];
		}
		if (is_string($value)) {
			if (strpos($value, '|') !== false) {
				$resolutionType = ResolveAdaptorInterface::TYPE_PIPELINE;
				return $this->resolveMiddlewarePipeline($value);
			} elseif (strpos($value, '::') !== false) {
				$resolutionType = ResolveAdaptorInterface::TYPE_STATIC;
				return $this->resolveStatic($value);
			} elseif (strpos($value, '->') !== false) {
				$resolutionType = ResolveAdaptorInterface::TYPE_INSTANCE;
				return $this->resolveInstanceMethod($value);
			} else {
				$resolutionType = ResolveAdaptorInterface::TYPE_INVOKE;
				return $this->resolveInvokable($value);
			}
		} else {
			$resolutionType = ResolveAdaptorInterface::TYPE_ORIGINAL;
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
		$instantiator = $this->instantiator;
		$middleware = $instantiator(\Weave\Middleware\Middleware::class);
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
		return function (...$params) use ($value) {
			return call_user_func_array($value, $params);
		};
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
		$instantiator = $this->instantiator;
		$callable[0] = $instantiator($callable[0]);
		return function (...$params) use ($callable) {
			return call_user_func_array($callable, $params);
		};
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
		$instantiator = $this->instantiator;
		return $instantiator($value);
	}
}
