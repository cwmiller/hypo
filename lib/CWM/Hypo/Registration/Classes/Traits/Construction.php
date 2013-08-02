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

namespace CWM\Hypo\Registration\Classes\Traits;

use CWM\Hypo\Registration\ClassRegistration;
use CWM\Hypo\Registration\Classes\LifeSpanStep;
use Closure;

/**
 * @package CWM\Hypo\Registration\Classes\Traits
 */
trait Construction {
	/**
	 * Sets arguments to be passed to the implementation's constructor
	 *
	 * @param $parameters
	 * @return LifeSpanStep
	 */
	public function withParameters($parameters) {
		$this->getRegistration()->setParameters($parameters);

		return new LifeSpanStep($this->getRegistration());
	}

	/**
	 * Sets a custom callback that is responsible for constructing the implementation.
	 * The callback will receive one argument: the name of the service being resolved.
	 *
	 * @param callable $closure
	 * @return LifeSpanStep
	 */
	public function constructedBy(Closure $closure) {
		$this->getRegistration()->setConstructedBy($closure);

		return new LifeSpanStep($this->getRegistration());
	}

	/**
	 * @return ClassRegistration
	 */
	abstract protected function getRegistration();
}