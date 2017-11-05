<?php
declare(strict_types = 1);
/**
 * Weave core.
 */

namespace Weave\Container;

/**
 * Weave core shared container trait requirements.
 */
trait Container
{
	/**
	 * Provide middleware pipeline sets.
	 *
	 * A Pipeline is a stack (usually array) of callables or strings that can be resolved to callables.
	 * The default pipeline and the only one required to be supported has a pipeline name of null.
	 *
	 * @param string $pipelineName The name of the pipeline to return a definition for.
	 *
	 * @return mixed Whatever the chosen Middleware stack uses for a pipeline of middlewares.
	 */
	abstract protected function provideMiddlewarePipeline($pipelineName = null);

	/**
	 * Setup routes for the Router.
	 *
	 * How routes are setup is Router-specific so see the Router docs for details.
	 *
	 * @param mixed $router The object to setup routes against - router specific.
	 *
	 * @return null
	 */
	abstract protected function provideRouteConfiguration($router);
}
