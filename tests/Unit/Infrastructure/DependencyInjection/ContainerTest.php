<?php

use TorrentPier\Infrastructure\DependencyInjection\Container;
use TorrentPier\Infrastructure\DependencyInjection\ContainerFactory;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

describe('Container', function () {
    beforeEach(function () {
        $this->container = $this->createTestContainer();
    });

    it('implements PSR-11 ContainerInterface', function () {
        expect($this->container)->toBeInstanceOf(\Psr\Container\ContainerInterface::class);
    });

    describe('get() method', function () {
        it('can resolve a simple service', function () {
            $container = $this->createContainerWithDefinitions([
                'test.service' => \DI\factory(function () {
                    return 'test_value';
                }),
            ]);

            $result = $container->get('test.service');
            expect($result)->toBe('test_value');
        });

        it('can resolve autowired classes', function () {
            $container = $this->createContainerWithDefinitions([
                'test.class' => \DI\autowire(stdClass::class),
            ]);

            $result = $container->get('test.class');
            expect($result)->toBeInstanceOf(stdClass::class);
        });

        it('throws NotFoundExceptionInterface for non-existent services', function () {
            expectException(
                fn() => $this->container->get('non.existent.service'),
                NotFoundExceptionInterface::class,
                'non.existent.service'
            );
        });

        it('returns same instance for singleton services', function () {
            $container = $this->createContainerWithDefinitions([
                'singleton.service' => \DI\factory(function () {
                    return new stdClass();
                }),
            ]);

            $instance1 = $container->get('singleton.service');
            $instance2 = $container->get('singleton.service');

            expect($instance1)->toBe($instance2);
        });
    });

    describe('has() method', function () {
        it('returns true for existing services', function () {
            $container = $this->createContainerWithDefinitions([
                'existing.service' => \DI\factory(function () {
                    return 'value';
                }),
            ]);

            expect($container->has('existing.service'))->toBeTrue();
        });

        it('returns false for non-existent services', function () {
            expect($this->container->has('non.existent.service'))->toBeFalse();
        });

        it('returns true for autowirable classes', function () {
            expect($this->container->has(stdClass::class))->toBeTrue();
        });
    });

    describe('make() method', function () {
        it('can make instances with parameters', function () {
            $result = $this->container->make(stdClass::class);
            expect($result)->toBeInstanceOf(stdClass::class);
        });

        it('creates new instances each time', function () {
            $instance1 = $this->container->make(stdClass::class);
            $instance2 = $this->container->make(stdClass::class);

            expect($instance1)->not->toBe($instance2);
        });
    });

    describe('call() method', function () {
        it('can call closures with dependency injection', function () {
            $result = $this->container->call(function (stdClass $class) {
                return get_class($class);
            });

            expect($result)->toBe('stdClass');
        });

        it('can call methods with parameters', function () {
            $service = new class {
                public function test(string $param): string
                {
                    return "Hello $param";
                }
            };

            $result = $this->container->call([$service, 'test'], ['param' => 'World']);
            expect($result)->toBe('Hello World');
        });
    });

    describe('injectOn() method', function () {
        it('returns the object after injection', function () {
            $object = new stdClass();

            $result = $this->container->injectOn($object);
            expect($result)->toBe($object);
        });
    });

    describe('getWrappedContainer() method', function () {
        it('returns the underlying PHP-DI container', function () {
            $wrapped = $this->container->getWrappedContainer();
            expect($wrapped)->toBeInstanceOf(\DI\Container::class);
        });

        it('allows direct access to PHP-DI functionality', function () {
            $wrapped = $this->container->getWrappedContainer();
            $wrapped->set('direct.service', 'direct_value');

            expect($this->container->get('direct.service'))->toBe('direct_value');
        });
    });

    describe('error handling', function () {
        it('provides meaningful error messages for missing services', function () {
            expectException(
                fn() => $this->container->get('missing.service'),
                NotFoundExceptionInterface::class,
                'missing.service'
            );
        });

        it('handles circular dependencies gracefully', function () {
            $container = $this->createContainerWithDefinitions([
                'service.a' => \DI\factory(function (\Psr\Container\ContainerInterface $c) {
                    return $c->get('service.b');
                }),
                'service.b' => \DI\factory(function (\Psr\Container\ContainerInterface $c) {
                    return $c->get('service.a');
                }),
            ]);

            expectException(
                fn() => $container->get('service.a'),
                ContainerExceptionInterface::class,
                'Circular dependency'
            );
        });
    });
});
