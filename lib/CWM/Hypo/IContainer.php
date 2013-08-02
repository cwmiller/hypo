<?php
/*
 * Copyright (c) 2013 Chase Miller

 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace CWM\Hypo;

use CWM\Hypo\Registration\ResolutionStep;

/**
 * @package CWM\Hypo
 */
interface IContainer {
	/**
	 * @abstract
	 * @param string $className
	 * @return ResolutionStep
	 */
	public function register($className);

	/**
	 * @abstract
	 * @param $instance
	 * @return ResolutionStep
	 */
	public function registerInstance($instance);

	/**
	 * @abstract
	 * @param string $service
	 * @return object|null
	 */
	public function resolve($service);

	/**
	 * @abstract
	 * @param string $name
	 * @return mixed
	 */
	public function resolveByName($name);

	/**
	 * @param $service
	 * @return array
	 */
	public function resolveAll($service);
}