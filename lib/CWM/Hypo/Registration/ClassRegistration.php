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

use \ReflectionClass;

/**
 * Data model for a single registration of a class.
 *
 * @package CWM\Hypo
 */
class ClassRegistration extends RegistrationBase {
	/**
	 * @var string
	 */
	protected $_implementation;

	/**
	 * @var bool
	 */
	protected $_isSingleton = false;

	/**
	 * @var array
	 */
	protected $_parameters = array();

	/**
	 * @var object|null
	 */
	protected $_instance = NULL;

	/**
	 * @param string $implemenation
	 */
	public function __construct($implemenation) {
		$this->_services = array($implemenation);
		$this->_implementation = $implemenation;
	}

	/**
	 * @return string
	 */
	public function getImplementation() {
		return $this->_implementation;
	}

	/**
	 * @param $parameters
	 * @return $this
	 */
	public function addParameters($parameters) {
		$this->_parameters = $parameters;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getParameters() {
		return $this->_parameters;
	}

	/**
	 * @param $singleton
	 * @return $this
	 */
	public function setIsSingleton($singleton) {
		$this->_isSingleton = $singleton;

		return $this;
	}

	public function isSingleton() {
		return $this->_isSingleton;
	}

	/**
	 * @param $instance
	 */
	public function setInstance($instance) {
		$this->_instance = $instance;
	}

	/**
	 * @return null|object
	 */
	public function getInstance() {
		return $this->_instance;
	}
}