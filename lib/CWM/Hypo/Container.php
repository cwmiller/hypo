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
use CWM\Hypo\Registration\ResolutionStep;

/**
 * Class Container
 * @package CWM\Hypo
 */
class Container implements IContainer {
	/**
	 * @var Registration[] $_registrations
	 */
	private $_registrations = array();

	/**
	 * Begins the registration process for the given class name using fluent API.
	 *
	 * The following will register requests for IService to be satisfied by a singleton of Service:
	 *
	 * $container
	 * 	->register(Service::class)
	 * 	->with(IService::class)
	 *	->asSingleton()
	 *
	 * @param string $type Class name to register
	 * @return ResolutionStep
	 */
	public function register($type) {
		// Start with a clean registration.
		$registration = new Registration();

		// Add the given class type as the implementation
		$registration->addImplementation($type);

		// Track the registration
		array_unshift($this->_registrations, $registration);

		// Continue to the next step in the fluent API
		return new ResolutionStep($registration);
	}

	/**
	 * Resolves a registered service class. Requesting a service that is not registered will return NULL.
	 * If multiple registrations are present for the service, then the last one to be configured will be returned.
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
				// Prevent any further searching
				break;
			}
		}

		return $instance;
	}

	/**
	 * Resolve a registration by name.
	 *
	 * For example:
	 * $container->register(Service::class)->asName('foo');
	 *
	 * $container->resolveByName('foo') => instance of Service
	 *
	 * @param string $name
	 * @return null|object
	 */
	public function resolveByName($name) {
		$instance = NULL;

		// Search all registrations for one that matches $name
		foreach ($this->_registrations as $registration) {
			if ($registration->getName() == $name) {
				$instance = $this->resolveRegistration($registration);
				// Prevent any further searching
				break;
			}
		}

		return $instance;
	}

	/**
	 * Resolves a registered service class. An instance is made for every registration found for the service class,
	 * and all instances will be returned as an array.
	 *
	 * @param string $service
	 * @return array
	 */
	public function resolveAll($service) {
		$instances = array();

		// Cycle all the configured registrations
		foreach ($this->_registrations as $registration) {
			$services = $registration->getServices();

			// If a matching service was found, resolve it and add it to the list of instances to be returned
			if (in_array($service, $services)) {
				$instances []= $this->resolveRegistration($registration);
			}
		}

		return $instances;
	}

	/**
	 * Resolves a Registration object
	 *
	 * @param Registration $registration
	 * @return object
	 */
	protected function resolveRegistration(Registration $registration) {
		// Registrations hold instances of singletons, so check that first
		if (!is_null($registration->getInstance())) {
			return $registration->getInstance();
		} else {
			// Get the name of the class to be constructed
			$className = $registration->getImplementation();

			// Get the named parameters to be passed to the constructor
			$configured_params = $registration->getParameters();

			// Reflect the class to be constructed to get information on the constructor
			$clazz = new \ReflectionClass($className);
			$constructor = $clazz->getConstructor();

			// Associative array for housing named parameters to be passed to the constructor
			$params_for_construction = array();

			if (!is_null($constructor)) {
				if ($constructor->getNumberOfParameters() > 0) {
					// Get details on each parameter in the constructor
					$params = $constructor->getParameters();

					foreach ($params as $param) {
						$value = NULL;

						// Check the parameter name against the configured parameter listing.
						if (array_key_exists($param->getName(), $configured_params)) {
							// The value can be anything, but if it's an instance of NamedDependency, then
							// the value must be resolved using the container.
							$value = $configured_params[$param->getName()];

							if ($value instanceof NamedDependency) {
								$value = $this->resolveByName($value->getName());
							}
						} else {
							// If the parameter is not explicitly configured, but is a class, then it will be resolved
							// using the container.
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

			// Construct the implementation.
			$instance = $clazz->newInstanceArgs($params_for_construction);

			// If a singleton, store the instance. It'll be served for any further requests of this service.
			if ($registration->isSingleton()) {
				$registration->setInstance($instance);
			}

			return $instance;
		}
	}
}