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

namespace CWM\Hypo\Registration;

abstract class RegistrationBase {
	/**
	 * @var array
	 */
	protected $_services = array();

	/**
	 * @var string
	 */
	protected $_name = NULL;

	/**
	 * @param string $service
	 * @return $this
	 */
	public function addService($service) {
		$this->_services []= $service;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getServices() {
		return $this->_services;
	}

	/**
	 * @param $name
	 * @return $this
	 */
	public function setName($name) {
		$this->_name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}
}