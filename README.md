# Hypo

Hypo is a Dependency Injection Container for PHP 5.4+. It provides a simple, fluent API for configuration. Currently, Hypo
only supports Constructor Injection.

## Installation

Hypo can easily be added to your project via [Composer](http://getcomposer.org/):

```javascript
"require": {
    "cwm/hypo": "1.2.2"
},
```

## Examples

### Registering a Class

```php
class Crypt {
    public function crypt($password) {
        return md5($password);
    }
}

$container = new Container();
$container->register('Crypt');

$crypt = $container->resolve('Crypt');
echo $crypt->crypt('testing');
```

### Registering a Class for an Interface

```php
interface ICrypt {
    public function crypt($password);
}

class Crypt implements ICrypt {
    public function crypt($password) {
        return md5($password);
    }
}

$container = new Container();
$container
    ->register('Crypt')
    ->with('ICrypt');

$crypt = $container->resolve('ICrypt');
```

### Registering a Class for Multiple Interfaces

```php
interface ICrypt {
    public function crypt($password);
}

interface IPasswordResetter {
    public function reset($user, $password);
}

class Crypt implements ICrypt, IPasswordResetter {
    public function crypt($password) {
        return md5($password);
    }

    public function reset($user, $password) {
        $user->setPassword($this->crypt($password));
    }
}

$container = new Container();
$container
    ->register('Crypt')
    ->with(array('ICrypt', 'IPasswordResetter'));
```

### Registering a Class for All Implemented Interfaces

```php
interface ICrypt {
    public function crypt($password);
}

interface IPasswordResetter {
    public function reset($user, $password);
}

class Crypt implements ICrypt, IPasswordResetter {
    public function crypt($password) {
        return md5($password);
    }

    public function reset(User $user, $password) {
        $user->setPassword($this->crypt($password));
    }
}

$container = new Container();
$container
    ->register('Crypt')
    ->withImplementedInterfaces();
```

### Registering Instances

Previously instantiated objects can be registered with the `registerInstance()` method. By default, they will be resolved for requests
of the class the object instantiated.

```php
$container = new Container();
$container
    ->register($instanceOfCrypt)
    ->withImplementedInterfaces();
```

### Configuring Constructor Arguments

Type hinted arguments are automatically resolved if they are classes or interfaces. This can be overridden by using
the NamedDependency feature (see next section). Primitive arguments on the other hand have to be specified if they do not have a default value.

```php
interface ICrypt {
    public function crypt($password);
}

class BCrypt implements ICrypt {
    public function __construct($workfactor) {
        ...
    }
}

$container = new Container();
$container
    ->register('BCrypt')
    ->with('ICrypt')
    ->withParameters(array(
        'workfactor' => 10
    ));
```

### Custom Construction via Closure

If the container is not suitable for handling the construction of an object, then it can be customized by use of the
`constructedBy()` method on the fluent API. The closure is passed the name of the class and expects an instance of it
to be returned.

```php
$container = new Container();
$container
    ->register('Crypt')
    ->with('ICrypt')
    ->constructedBy(function($className) {
        return new $className(rand());
    });
```

### Naming Registrations

When multiple classes are registered for the same implementation, they can each be given a unique name. This name can be used
to resolve the class by using the `resolveName()` method.

```php
interface ICrypt {
    public function crypt($password);
}

class BCrypt implements ICrypt {
    public function __construct($workfactor) {
        ...
    }
}

class MD5Crypt implements ICrypt {
    ...
}

$container = new Container();
$container
    ->register('BCrypt')
    ->with('ICrypt')
    ->withParameters(array(
        'workfactor' => 10
    ))
    ->withName('bcrypt');

$container->resolveName('bcrypt');
```

Also, the name can be used with the `NamedDependency` class when configuring parameters:

```php
class UserService {
    public function __construct(ICrypt $crypt) {
        ...
    }
}

$container
    ->register('UserService')
    ->withParameters(array(
        'crypt' => new NamedDependency('bcrypt')
    ));
```

### Configuring Object Lifespan
Singletons can be set by using the `asSingleton()` method. `asTransient()` is also provided, but it is not required to
specify because all registrations are transient by default.

```php
interface IUserRepo {
    ...
}

class UserRepo implements IUserRepo {
    ...
}

$container = new Container();
$container
    ->register('UserRepo')
    ->with('IUserRepo')
    ->asSingleton();
```

## License

Copyright (c) 2013 Chase Miller

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.