# Hypo

Hypo is a Dependency Injection Container for PHP 5.4+. It provides a simple, fluent API for configuration. Currently, Hypo
only supports Constructor Injection.

## Examples

### Registering a Class

    class Crypt {
        public function crypt($password) {
            return md5($password);
        }
    }

    $container = new Container();
    $container->register('Crypt');

    $crypt = $container->resolve('Crypt');
    echo $crypt->crypt('testing');

### Registering a Class for an Interface

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

### Registering a Class for Multiple Interfaces

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

### Registering a Class for All Implemented Interfaces

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

### Configuring Arguments

Type hinted arguments are automatically resolved if they are classes or interfaces. This can be overridden by using
the NamedDependency feature (see next section). Primitive arguments on the other hand have to be specified if they do not have a default value.

    interface ICrypt {
        public function crypt($password);
    }

    class BCrypt implements ICrypt {
        public function BCrypt($workfactor) {
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

### Naming Registrations

When multiple classes are registered for the same implementation, they can each be given a unique name. This name can be used
to resolve the class by using the `resolveName()` method.

    interface ICrypt {
        public function crypt($password);
    }

    class BCrypt implements ICrypt {
        public function BCrypt($workfactor) {
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

Also, the name can be used with the `NamedDependency` class when configuring parameters:

    class UserService {
        public function UserService(ICrypt $crypt) {
            ...
        }
    }

    $container
        ->register('UserService')
        ->withParameters(array(
            'crypt' => new NamedDependency('bcrypt')
        ));

### Lifespan
Singletons can be set by using the `asSingleton()` method. `asTransient()` is also provided, but it is not required to
specify because all registrations are transient by default.

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