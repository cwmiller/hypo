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

use CWM\Hypo\Registration\NamedDependency;
use CWM\Hypo\Registration;
use CWM\Hypo\Registration\FirstStep;

class Container implements IContainer {
	/**
	 * @var Registration[] $_registrations
	 */
	private $_registrations;

	/**
	 * Begins the registration process for the given class name using fluent API.
	 *
	 * The following will register requests for IService to be satisfied by a singleton of Service:
	 *
	 * $container
	 * 	->register(Service::class)
	 * 	->asType(IService::class)
	 *	->asSingleton()
	 *
	 * @param string $type Class name to register
	 * @return FirstStep
	 */
	public function register($type) {
		// Start with a clean registration.
		$registration = new Registration();

		// Add the given class type as the implementation
		$registration->addImplementation($type);

		// Track the registration
		$this->_registrations []= $registration;

		// Continue the fluent API
		return new FirstStep($registration);
	}

	/**
	 * Resolves a registered service class. Requesting a service that is not registered will return NULL.
	 *
	 * @param string $service Class name to resolve
	 * @return object|null
	 */
	public function resolve($service) {
		$instance = NULL;

		// Cycle all the configured registrations
		foreach ($this->_registrations as $registration) {
			$services = $registration->getServices();

			// If the requested service is found, return an instance of the configured implementation.
			if (in_array($service, $services)) {
				$instance = $this->resolveRegistration($registration);
				break;
			}
		}

		return $instance;
	}

	/**
	 * Resolve a registration by name.
	 *
	 * For example:
	 * $container->register(Service::class)->asName('testing');
	 *
	 * $container->resolveByName('testing') => instance of Service
	 *
	 * @param string $name
	 * @return null|object
	 */
	public function resolveByName($name) {
		$instance = NULL;

		foreach ($this->_registrations as $registration) {
			if ($registration->getName() == $name) {
				$instance = $this->resolveRegistration($registration);
			}
		}

		return $instance;
	}

	/**
	 * @param Registration $registration
	 * @return object
	 */
	protected function resolveRegistration(Registration $registration) {
		if (!is_null($registration->getInstance())) {
			return $registration->getInstance();
		} else {
			$className = $registration->getImplementation();
			$configured_params = $registration->getParameters();

			$clazz = new \ReflectionClass($className);
			$constructor = $clazz->getConstructor();
			$params_for_construction = array();

			if (!is_null($constructor)) {
				if ($constructor->getNumberOfParameters() > 0) {
					$params = $constructor->getParameters();

					foreach ($params as $param) {
						$value = NULL;

						if (array_key_exists($param->getName(), $configured_params)) {
							$value = $configured_params[$param->getName()];

							// If NamedDependency, resolve it
							if ($value instanceof NamedDependency) {
								$value = $this->resolveByName($value->getName());
							}

						} else {
							$param_clazz = $param->getClass();

							if (!is_null($param_clazz)) {
								$param_instance = $this->resolve($param_clazz->getName());
								if (!is_null($param_instance)) {
									$value = $param_instance;
								}
							}
						}

						$params_for_construction[$param->getName()] = $value;
					}
				}
			}

			$instance = $clazz->newInstanceArgs($params_for_construction);

			if ($registration->isSingleton()) {
				$registration->setInstance($instance);
			}

			return $instance;
		}
	}
}