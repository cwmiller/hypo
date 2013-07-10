# Hypo

Hypo is a Dependency Injection container for PHP 5.4+. It provides a simple, fluent API for configuration.

## Features
-   Simple configuration via Fluent API
-   Constructor injection (setter injection possibly in the future)
-   Named dependencies

##Examples

### Registering a Class

Code:
    class Crypt {
        public function crypt($password) {
            return md5($password);
        }
    }

    $container = new Container();
    $container->register('Crypt');
    $crypt = $container->resolve('Crypt');
    echo $crypt->crypt('testing');

Output:
    ae2b1fca515949e5d54fb22b8ed95575

### Registering a Class for Interface
Code:
    interface ICrypt {
        public function crypt($password);
    }

    class Crypt implements ICrypt {
        public function crypt($password) {
            return md5($password);
        }
    }

    $container = new Container();
    $container->register('Crypt')->with('ICrypt');

    $crypt = $container->resolve('Crypt');
    $icrypt = $container->resolve('ICrypt');

    echo $crypt->crypt('testing')
        . ' = '
        . $icrypt->crypt('testing');

Output:
    ae2b1fca515949e5d54fb22b8ed95575 = ae2b1fca515949e5d54fb22b8ed95575

### Registering a Class for Multiple Interfaces
Code:
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
    $container->register('Crypt')->with(array('ICrypt', 'IPasswordResetter'));

### Registering a Class for All Implemented Interfaces
Code:
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
    $container->register('Crypt')->withAllImplementedInterfaces();

### Typed Dependencies Will Automatically be Resolved
Code:
    interface IUserService {
        ...
    }

    class UserService implements IUserService {
        ...
    }

    class UserController {
        public function __construct(IUserService $userService) {
            ...
        }
    }

    $container = new Container();
    $container->register('UserService')->with('IUserService');
    $container->register('UserController');

    $container->resolve('UserController');