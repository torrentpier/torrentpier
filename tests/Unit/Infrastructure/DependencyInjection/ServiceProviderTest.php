<?php

use TorrentPier\Infrastructure\DependencyInjection\Container;
use TorrentPier\Infrastructure\DependencyInjection\ServiceProvider;

describe('ServiceProvider interface', function () {
    it('defines required methods', function () {
        $reflection = new ReflectionClass(ServiceProvider::class);

        expect($reflection->isInterface())->toBeTrue();
        expect($reflection->hasMethod('register'))->toBeTrue();
        expect($reflection->hasMethod('boot'))->toBeTrue();
    });

    it('register method has correct signature', function () {
        $reflection = new ReflectionClass(ServiceProvider::class);
        $method = $reflection->getMethod('register');

        expect($method->isPublic())->toBeTrue();
        expect($method->getParameters())->toHaveCount(1);
        expect($method->getParameters()[0]->getType()?->getName())->toBe(Container::class);
        expect($method->getReturnType()?->getName())->toBe('void');
    });

    it('boot method has correct signature', function () {
        $reflection = new ReflectionClass(ServiceProvider::class);
        $method = $reflection->getMethod('boot');

        expect($method->isPublic())->toBeTrue();
        expect($method->getParameters())->toHaveCount(1);
        expect($method->getParameters()[0]->getType()?->getName())->toBe(Container::class);
        expect($method->getReturnType()?->getName())->toBe('void');
    });
});

describe('ServiceProvider implementation examples', function () {
    it('can implement a basic service provider', function () {
        $provider = new class implements ServiceProvider {
            public function register(Container $container): void
            {
                $container->getWrappedContainer()->set('example.service', 'registered');
            }

            public function boot(Container $container): void
            {
                // Boot logic here
            }
        };

        $container = $this->createTestContainer();

        $provider->register($container);

        expect($container->get('example.service'))->toBe('registered');
    });

    it('can implement a provider with complex services', function () {
        $provider = new class implements ServiceProvider {
            public function register(Container $container): void
            {
                $container->getWrappedContainer()->set('complex.service', \DI\factory(function () {
                    return new class {
                        public function getValue(): string
                        {
                            return 'complex_value';
                        }
                    };
                }));
            }

            public function boot(Container $container): void
            {
                // Could perform additional setup here
                $service = $container->get('complex.service');
                // Setup complete
            }
        };

        $container = $this->createTestContainer();

        $provider->register($container);
        $provider->boot($container);

        $service = $container->get('complex.service');
        expect($service->getValue())->toBe('complex_value');
    });

    it('can implement a provider that registers multiple services', function () {
        $provider = new class implements ServiceProvider {
            public function register(Container $container): void
            {
                $wrapped = $container->getWrappedContainer();

                $wrapped->set('service.a', 'value_a');
                $wrapped->set('service.b', 'value_b');
                $wrapped->set('service.c', \DI\factory(function () {
                    return 'value_c';
                }));
            }

            public function boot(Container $container): void
            {
                // Boot all registered services
            }
        };

        $container = $this->createTestContainer();

        $provider->register($container);
        $provider->boot($container);

        expect($container->get('service.a'))->toBe('value_a');
        expect($container->get('service.b'))->toBe('value_b');
        expect($container->get('service.c'))->toBe('value_c');
    });

    it('boot method can access services registered by register method', function () {
        $bootedServices = [];

        $provider = new class($bootedServices) implements ServiceProvider {
            public function __construct(private array &$bootedServices)
            {
            }

            public function register(Container $container): void
            {
                $container->getWrappedContainer()->set('bootable.service', 'registered_value');
            }

            public function boot(Container $container): void
            {
                $value = $container->get('bootable.service');
                $this->bootedServices[] = $value;
            }
        };

        $container = $this->createTestContainer();

        $provider->register($container);
        $provider->boot($container);

        expect($bootedServices)->toBe(['registered_value']);
    });
});
