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

use \ReflectionClass;

/**
 * Data model for a single registration's configuration.
 *
 * @package CWM\Hypo
 */
class Registration {
	/**
	 * @var array
	 */
	private $_services = array();

	/**
	 * @var object|null
	 */
	private $_implementation;

	/**
	 * @var string
	 */
	private $_name = NULL;

	/**
	 * @var bool
	 */
	private $_isSingleton = false;

	/**
	 * @var array
	 */
	private $_parameters = array();

	/**
	 * @var object
	 */
	private $_instance = NULL;

	public function __construct() {
		$this->_services = array();
		$this->_implementation = NULL;
	}

	/**
	 * @param string $implementation
	 * @return $this
	 */
	public function addImplementation($implementation) {
		$this->_implementation = $implementation;
		$this->_services []= $implementation;

		return $this;
	}

	/**
	 * @return null|object
	 */
	public function getImplementation() {
		return $this->_implementation;
	}

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