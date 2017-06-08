<?php
namespace Flashy\Test;
use DI\Container;
use DI\ContainerBuilder;
use Flashy\App;
use Flashy\ServiceProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use function DI\object;

class AppTest extends TestCase {
    public function testBuildContainer() {
        $app = new App();
        $container = $app->buildContainer();
        $this->assertInstanceOf(Container::class, $container);
    }

    public function testGetContainer() {
        $app = new App();
        $container = $app->buildContainer();
        $get_container = $app->getContainer();
        $this->assertSame($container, $get_container);
    }

    public function testRegisterService() {
        $app = new App();
        $app->register(new DumbService(), [
            'test' => 'Ok'
        ]);
        $container = $app->buildContainer();
        $this->assertInstanceOf(B::class, $container->get(A::class));
        $this->assertEquals('Ok', $container->get('test'));
    }

    public function testConfigureContainerBuilder() {
        $app = new App();
        $app->register(new DumbService(), [
            'test' => 'Ok'
        ]);
        $app->configureContainerBuilder(function($builder) {
            $builder->addDefinitions([
                'test' => 'lol',
                A::class => object(C::class)
            ]);
        });
        $container = $app->buildContainer();

        $this->assertInstanceOf(C::class, $container->get(A::class));
        $this->assertEquals('lol', $container->get('test'));
    }

    public function testRun() {
        $app = new App();
        $app->register(new DumbService(), [
            'test' => 'Ok'
        ]);
        $result = $app->run(DumbApplication::class);
        $this->assertEquals('Ok', $result);
    }

}

class A {}
class B extends A {}
class C extends A {}

class DumbService implements ServiceProviderInterface {
    public function register(ContainerBuilder $builder, array $opts = []) : void {
        $def = array_merge([
            'test' => 'Flashy',
        ], $opts);

        $def[A::class] = object(B::class);

        $builder->addDefinitions($def);
    }
}

class DumbApplication {
    public function run(ContainerInterface $c) {
        return $c->get('test');
    }
}
