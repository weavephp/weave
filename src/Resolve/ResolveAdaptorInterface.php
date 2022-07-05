<?php

declare(strict_types = 1);

/**
 * Weave Core.
 */
namespace Weave\Resolve;

/**
 * Weave core resolver used by the Router and Dispatcher classes.
 */
interface ResolveAdaptorInterface
{
	/**
	 * Consts representing what kind of resolution was performed.
	 */
	const TYPE_PIPELINE = 'pipeline';
	const TYPE_STATIC = 'static';
	const TYPE_INSTANCE = 'instance';
	const TYPE_INVOKE = 'invoke';
	const TYPE_ORIGINAL = 'original';

	/**
	 * Attempt to convert a provided value into a callable.
	 *
	 * If the value isn't a string it is simply returned.
	 * If the string value contains '|' treat it as a pipeline name.
	 * If the string value contains '::' treat it as a static method call.
	 * If the string value contains '->' treat it as an instance method call.
	 * Otherwise, attempt to treat it as an invokable.
	 *
	 * @param string|callable $value           The value to resolve.
	 * @param string          $resolutionType Set to the type of resolution identified.
	 *
	 * @return callable Usually some form of callable.
	 */
	public function resolve(string|callable $value, string &$resolutionType): callable;

	/**
	 * Return the array of remaining dispatch steps with the first step removed (shifted).
	 *
	 * A dispatch array can contain multiple middleware pipe names separated by a '|' char
	 * which can be progressively consumed by Dispatch middlewares. This method removes a
	 * single dispatch pipeline step, returning the remaining array.
	 *
	 * If something other than an array is passed in then return an empty array.
	 *
	 * @param array|string|callable $value The dispatch steps.
	 *
	 * @return array The array of remaining dispatch steps.
	 */
	public function shift(array|string|callable $value): array;
}
