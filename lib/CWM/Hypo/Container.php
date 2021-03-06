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

use CWM\Hypo\Registration\Classes\NamedDependency;
use CWM\Hypo\Registration\RegistrationBase;
use CWM\Hypo\Registration\ClassRegistration;
use CWM\Hypo\Registration\InstanceRegistration;
use CWM\Hypo\Registration\Classes\ResolutionStep;
use CWM\Hypo\Registration\Instances\ResolutionStep as InstanceResolutionStep;
use CWM\Hypo\Exceptions\NotRegisteredException;
use InvalidArgumentException;

/**
 * Class Container
 * @package CWM\Hypo
 */
class Container implements IContainer {
	/**
	 * @var RegistrationBase[] $_registrations
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
	 * @param string $className Class name to register
	 * @return ResolutionStep
	 * @throws InvalidArgumentException
	 */
	public function register($className) {
		if (!is_string($className)) {
			throw new InvalidArgumentException('Class name must be a string.', 'className');
		}

		// Start with a clean registration.
		$registration = new ClassRegistration($className);

		// Track the registration
		array_unshift($this->_registrations, $registration);

		// Continue to the next step in the fluent API
		return new ResolutionStep($registration);
	}

	/**
	 * Registers a previously initialized object.
	 *
	 * @param object $instance
	 * @return InstanceResolutionStep
	 * @throws InvalidArgumentException
	 */
	public function registerInstance($instance) {
		if (!is_object($instance)) {
			throw new InvalidArgumentException('Instance must be an instance of an object.', 'instance');
		}

		// Start with a clean registration.
		$registration = new InstanceRegistration($instance);

		// Track the registration
		array_unshift($this->_registrations, $registration);

		// Continue to the next step in the fluent API
		return new InstanceResolutionStep($registration);
	}

	/**
	 * Resolves a registered service class. Requesting a service that is not registered will throw a NotRegisteredException.
	 * If multiple registrations are present for the service, then the last one to be configured will be returned.
	 *
	 * @param string $service Class name to resolve
	 * @return object
     * @throws NotRegisteredException
	 */
	public function resolve($service) {
        $registrations = array_values(
            array_filter($this->_registrations, function(RegistrationBase $registration) use ($service) {
                return in_array($service, $registration->getServices());
            }));

        if (count($registrations) == 0) {
            throw new NotRegisteredException($service, $service . ' not registered with container.');
        }

        return $this->resolveRegistration($registrations[0]);
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
	 * @return object
     * @throws NotRegisteredException
	 */
	public function resolveByName($name) {
        $registrations = array_values(
            array_filter($this->_registrations, function(RegistrationBase $registration) use ($name) {
                return $registration->getName() == $name;
            }));

        if (count($registrations) == 0) {
            throw new NotRegisteredException($name, 'Name "' . $name . '"" not registered with container.');
        }

        return $this->resolveRegistration($registrations[0]);
	}

	/**
	 * Resolves a registered service class. An instance is made for every registration found for the service class,
	 * and all instances will be returned as an array.
	 *
	 * @param string $service
	 * @return array
     * @throws NotRegisteredException
	 */
	public function resolveAll($service) {
        $registrations = array_values(
            array_filter($this->_registrations, function(RegistrationBase $registration) use ($service) {
                return in_array($service, $registration->getServices());
            }));

        if (count($registrations) == 0) {
            throw new NotRegisteredException($service, $service . ' not registered with container.');
        }

        return array_map(function(RegistrationBase $registration) {
            return $this->resolveRegistration($registration);
        }, $registrations);
	}

	/**
	 * Resolves a RegistrationBase object
	 *
	 * @param RegistrationBase $registration
	 * @return null|object
	 */
	protected function resolveRegistration(RegistrationBase $registration) {
		if ($registration instanceof ClassRegistration) {
			return $this->resolveClassRegistration($registration);
		} else if ($registration instanceof InstanceRegistration) {
			return $this->resolveInstanceRegistration($registration);
		} else {
			return null;
		}
	}

	/**
	 * Resolves a ClassRegistration object
	 *
	 * @param ClassRegistration $registration
	 * @return object
	 */
	protected function resolveClassRegistration(ClassRegistration $registration) {
		// Registrations hold instances of singletons, so check that first
		if (!is_null($registration->getInstance())) {
			return $registration->getInstance();
		} else {
			// Get the class name of the implementation to be constructed
			$class_name = $registration->getImplementation();

			$instance = null;

			// If a callback was given for construction, call it
			if (!is_null($registration->getConstructedBy())) {
				$callback = $registration->getConstructedBy();

				$instance = $callback($class_name);
			} else {
				// Get the named parameters to be passed to the constructor
				$parameters = $registration->getParameters();

				// Construct implementation
				$instance = $this->constructImplementation($class_name, $parameters);
			}

			// If a singleton, store the instance. It'll be served for any further requests of this service.
			if ($registration->isSingleton()) {
				$registration->setInstance($instance);
			}

			return $instance;
		}
	}

	/**
	 * Constructs the given class by passing the given parameters to the constructor
	 *
	 * @param $className
	 * @param $parameters
	 * @return object
	 */
	protected function constructImplementation($className, $parameters = array()) {
		// Reflect the class to be constructed to get information on the constructor
		$clazz = new \ReflectionClass($className);
		$constructor = $clazz->getConstructor();

		// Associative array for housing named parameters to be passed to the constructor
		$params_for_construction = array();

		if (!is_null($constructor)) {
			if ($constructor->getNumberOfParameters() > 0) {
				// Get details on each parameter in the constructor
				$parameters_on_constructor = $constructor->getParameters();

				foreach ($parameters_on_constructor as $param) {
					$value = NULL;

					// Check the parameter name against the configured parameter listing.
					if (array_key_exists($param->getName(), $parameters)) {
						// The value can be anything, but if it's an instance of NamedDependency, then
						// the value must be resolved using the container.
						$value = $parameters[$param->getName()];

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

		// Construct the implementation and pass it back
		return $clazz->newInstanceArgs($params_for_construction);
	}

	/**
	 * Resolves a InstanceRegistration object
	 *
	 * @param InstanceRegistration $registration
	 * @return object
	 */
	protected function resolveInstanceRegistration(InstanceRegistration $registration) {
		// Instance-based registrations simply return the implementation
		return $registration->getImplementation();
	}
}