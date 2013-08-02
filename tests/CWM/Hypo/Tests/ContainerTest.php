<?php
namespace CWM\Hypo\Tests;

use CWM\Hypo\Container;
use CWM\Hypo\Exceptions\RegistrationException;
use CWM\Hypo\Registration\Classes\NamedDependency;

class ContainerTest extends \PHPUnit_Framework_TestCase {
	public function testResolve() {
		$container = new Container();
		$container->register('CWM\Hypo\Tests\Dummy');

		$this->assertInstanceOf('CWM\Hypo\Tests\Dummy', $container->resolve('CWM\Hypo\Tests\Dummy'));
	}

	public function testResolveWithNull() {
		$container = new Container();

		$this->assertNull($container->resolve('CWM\Hypo\Tests\Dummy'));
	}

	public function testResolveWithInterface() {
		$container = new Container();
		$container->register('CWM\Hypo\Tests\Dummy')->with('CWM\Hypo\Tests\IDummy');

		$this->assertInstanceOf('CWM\Hypo\Tests\Dummy', $container->resolve('CWM\Hypo\Tests\IDummy'));
	}

	public function testResolveWithDuplicateInterfaces() {
		$container = new Container();
		$container->register('CWM\Hypo\Tests\Dummy')->with('CWM\Hypo\Tests\IDummy');
		$container->register('CWM\Hypo\Tests\DumDum')->with('CWM\Hypo\Tests\IDummy');

		$this->assertInstanceOf('CWM\Hypo\Tests\DumDum', $container->resolve('CWM\Hypo\Tests\IDummy'));
	}

	public function testResolveWithAllInterfaces() {
		$container = new Container();
		$container->register('CWM\Hypo\Tests\ImplementsBoth')->withImplementedInterfaces();

		$this->assertInstanceOf('CWM\Hypo\Tests\ImplementsBoth', $container->resolve('CWM\Hypo\Tests\IDummy'));
		$this->assertInstanceOf('CWM\Hypo\Tests\ImplementsBoth', $container->resolve('CWM\Hypo\Tests\IDummy'));
	}

	public function testResolveAll() {
		$container = new Container();
		$container->register('CWM\Hypo\Tests\Dummy')->with('CWM\Hypo\Tests\IDummy');
		$container->register('CWM\Hypo\Tests\DumDum')->with('CWM\Hypo\Tests\IDummy');

		$instances = $container->resolveAll('CWM\Hypo\Tests\IDummy');

		$this->assertCount(2, $instances);
		$this->assertInstanceOf('CWM\Hypo\Tests\DumDum', $instances[0]);
		$this->assertInstanceOf('CWM\Hypo\Tests\Dummy', $instances[1]);
	}

	/**
	 * @expectedException \CWM\Hypo\Exceptions\RegistrationException
	 */
	public function testRegisterWithIncompatibleTypes() {
		$container = new Container();
		$container->register('CWM\Hypo\Tests\Dummy')->with('CWM\Hypo\Tests\INotDummy');
	}

	public function testResolveWithSingleton() {
		$container = new Container();
		$container->register('CWM\Hypo\Tests\Dummy')->AsSingleton();

		$dummy1 = $container->resolve('CWM\Hypo\Tests\Dummy');
		$dummy2 = $container->resolve('CWM\Hypo\Tests\Dummy');

		$dummy1->setValue(12345);

		$this->assertEquals(12345, $dummy2->getValue());
	}

	public function testResolveWithTransient() {
		$container = new Container();
		$container->register('CWM\Hypo\Tests\Dummy')->asTransient();

		$dummy1 = $container->resolve('CWM\Hypo\Tests\Dummy');
		$dummy2 = $container->resolve('CWM\Hypo\Tests\Dummy');

		$dummy1->setValue(12345);

		$this->assertNotEquals(12345, $dummy2->getValue());
	}

	public function testResolveName() {
		$container = new Container();
		$container->register('CWM\Hypo\Tests\Dummy')->with('CWM\Hypo\Tests\IDummy')->withName('dummy');
		$container->register('CWM\Hypo\Tests\DumDum')->with('CWM\Hypo\Tests\IDummy')->withName('dumdum');

		$this->assertInstanceOf('CWM\Hypo\Tests\DumDum', $container->resolveByName('dumdum'));
	}

	public function testResolveWithDependencies() {
		$container = new Container();
		$container->register('CWM\Hypo\Tests\Dummy')->with('CWM\Hypo\Tests\IDummy');
		$container->register('CWM\Hypo\Tests\DependsOnIDummy');

		$instance = $container->resolve('CWM\Hypo\Tests\DependsOnIDummy');

		$this->assertInstanceOf('CWM\Hypo\Tests\Dummy', $instance->dummy);
	}

	public function testResolveWithParameters() {
		$container = new Container();
		$container->register('CWM\Hypo\Tests\Dummy')->with('CWM\Hypo\Tests\IDummy');
		$container->register('CWM\Hypo\Tests\DependsOnIDummy')
			->withParameters(array(
				'someValue' => 999
			));

		$instance = $container->resolve('CWM\Hypo\Tests\DependsOnIDummy');

		$this->assertEquals(999, $instance->someValue);
	}

	public function testResolveWithNamedDependency() {
		$container = new Container();
		$container->register('CWM\Hypo\Tests\Dummy')->with('CWM\Hypo\Tests\IDummy')->withName('dummy');
		$container->register('CWM\Hypo\Tests\DumDum')->with('CWM\Hypo\Tests\IDummy')->withName('dumdum');

		$container->register('CWM\Hypo\Tests\DependsOnIDummy')
			->withParameters(array(
				'dummy' => new NamedDependency('dummy')
			));

		$instance = $container->resolve('CWM\Hypo\Tests\DependsOnIDummy');

		$this->assertInstanceOf('CWM\Hypo\Tests\Dummy', $instance->dummy);
	}

	public function testRegisterInstance() {
		$container = new Container();
		$value = 42345234223423;

		$dummy = new Dummy();
		$dummy->setValue($value);

		$container->registerInstance($dummy);

		$this->assertEquals($value, $container->resolve('CWM\Hypo\Tests\Dummy')->getValue());
	}

	/**
	 * @expectedException \CWM\Hypo\Exceptions\RegistrationException
	 */
	public function testRegisterInstanceWithIncompatibleTypes() {
		$container = new Container();

		$dummy = new Dummy();

		$container->registerInstance($dummy)->with('CWM\Hypo\Tests\INotDummy');
	}

	public function testResolveNameWithInstance() {
		$container = new Container();
		$container->register('CWM\Hypo\Tests\Dummy')->with('CWM\Hypo\Tests\IDummy')->withName('dummy');

		$container->registerInstance(new DumDum())->withName('dumdum');

		$this->assertInstanceOf('CWM\Hypo\Tests\DumDum', $container->resolveByName('dumdum'));
	}

	public function testResolveWithCallback() {
		$container = new Container();
		$value = 53474534534;

		$container->register('CWM\Hypo\Tests\Dummy')->constructedBy(function($className) use ($value) {
			$dummy = new $className();
			$dummy->setValue($value);

			return $dummy;
		});

		$this->assertInstanceOf('CWM\Hypo\Tests\Dummy', $container->resolve('CWM\Hypo\Tests\Dummy'));
		$this->assertEquals($value, $container->resolve('CWM\Hypo\Tests\Dummy')->getValue());
	}
}

interface IDummy {
	public function setValue($value);
	public function getValue();
}

interface INotDummy {
}

class Dummy implements IDummy {
	private $_value;

	public function setValue($value) {
		$this->_value = $value;
	}

	public function getValue() {
		return $this->_value;
	}
}

class DumDum implements IDummy {
	public function setValue($value) {
	}

	public function getValue() {
		return null;
	}
}

class ImplementsBoth implements IDummy, INotDummy {
	public function setValue($value) {
	}

	public function getValue() {
	}
}

class DependsOnIDummy {
	public $dummy;
	public $someValue;

	public function __construct(IDummy $dummy, $someValue = 0) {
		$this->dummy = $dummy;
		$this->someValue = $someValue;
	}
}