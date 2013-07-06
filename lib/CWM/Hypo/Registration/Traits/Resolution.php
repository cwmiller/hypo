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

namespace CWM\Hypo\Registration\Traits;

use CWM\Hypo\Registration\SecondStep;
use CWM\Hypo\Registration\ThirdStep;
use CWM\Hypo\Registration;

trait Resolution {
	/**
	 * @return ThirdStep
	 */
	public function asImplementedInterfaces() {
		$this->getRegistration()->addInterfacesAsServices();

		return new SecondStep($this->getRegistration());
	}

	/**
	 * @param array|string $type
	 * @return ThirdStep
	 */
	public function asType($type) {
		if (!is_array($type)) {
			$type = array($type);
		}

		$this->getRegistration()->addServices(array($type));

		return new SecondStep($this->getRegistration());
	}

	/**
	 * @return Registration
	 */
	abstract protected function getRegistration();
}