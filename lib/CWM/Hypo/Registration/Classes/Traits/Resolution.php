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

use CWM\Hypo\Registration\Classes\LifeSpanStep;
use CWM\Hypo\Registration\Classes\ConstructionStep;
use CWM\Hypo\Registration\ClassRegistration;
use CWM\Hypo\Exceptions\RegistrationException;
use \ReflectionClass;

/**
 * @package CWM\Hypo\Registration\Classes\Traits
 */
trait Resolution {
	/**
	 * @return ConstructionStep
	 */
	public function withImplementedInterfaces() {
		$registration = $this->getRegistration();
		$implemenation = $registration->getImplementation();

		if (class_exists($implemenation)) {
			$clazz = new ReflectionClass($implemenation);
			$interfaces = $clazz->getInterfaceNames();

			foreach ($interfaces as $interface) {
				$this->with($interface);
			}
		}

		return new ConstructionStep($registration);
	}

	/**
	 * @param array|string $services
	 * @return ConstructionStep
	 * @throws RegistrationException
	 */
	public function with($services) {
		$registration = $this->getRegistration();
		$clazz = new ReflectionClass($registration->getImplementation());

		if (!is_array($services)) {
			$services = array($services);
		}

		foreach ($services as $service) {
			// Make sure service is compatible with implementation
			$service_clazz = new ReflectionClass($service);

			if ($service_clazz->getName() != $clazz->getName()) {
				if ($service_clazz->isInterface()) {
					if (!$clazz->implementsInterface($service_clazz)) {
						$message = sprintf('Interface %s is incompatible with class %s.', $service_clazz->getName(), $clazz->getName());

						throw new RegistrationException($registration, $message);
					}
				} else if (!$clazz->isSubclassOf($service_clazz)) {
					$message = sprintf('Class %s is incompatible with class %s.', $service_clazz->getName(), $clazz->getName());

					throw new RegistrationException($registration, $message);
				}
			}

			$registration->addService($service);
		}

		return new ConstructionStep($registration);
	}

	/**
	 * @return ClassRegistration
	 */
	abstract protected function getRegistration();
}