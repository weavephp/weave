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
	 * @param mixed  $value           The value to resolve. Usually a string or callable.
	 * @param string &$resolutionType Set to the type of resolution identified.
	 *
	 * @return mixed Usually some form of callable.
	 */
	public function resolve($value, &$resolutionType);

	/**
	 * Return the string of remaining dispatch steps with the first step removed (shifted).
	 *
	 * A dispatch string can contain multiple middleware pipe names separated by a '|' char
	 * which can be progressively consumed by Dispatch middlewares. This method removes a
	 * single dispatch pipeline step, returning the remaining string.
	 *
	 * If something other than a string is passed in then return an empty string.
	 *
	 * @param mixed $value The string of dispatch steps.
	 *
	 * @return string The string of remaining dispatch steps.
	 */
	public function shift($value);
}
