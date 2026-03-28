<?php

declare(strict_types=1);

namespace App\Controllers\Site {
    use Core\Http\Request;
    use Core\Http\Response;
    use Core\SiteController;

    final class RouterDynamicFixture extends SiteController
    {
        public function index(Request $request): Response
        {
            return Response::html('index');
        }

        public function show(Request $request, string $id): Response
        {
            return Response::html('show:' . $id);
        }

        protected function hidden(Request $request): Response
        {
            return Response::html('hidden');
        }
    }
}

namespace {
    use Core\Container;
    use Core\Http\Request;
    use Core\Router;
    use Core\View\View;

    require_once __DIR__ . '/bootstrap.php';

    $container = new Container();
    $viewRoot = sys_get_temp_dir() . '/router-view-fixture-' . bin2hex(random_bytes(4));
    @mkdir($viewRoot . '/layouts', 0777, true);
    file_put_contents($viewRoot . '/layouts/main.html', '{content}');
    $container->singleton('view.site', static fn () => new View($viewRoot, 'layouts/main'));

    $router = new Router($container, [], [
        'dynamic' => true,
        'default_method' => 'index',
        'namespaces' => [
            'site' => 'App\\Controllers\\Site',
            'admin' => 'App\\Controllers\\Admin',
            'api' => 'App\\Controllers\\Api',
        ],
    ]);

    $missingMethod = $router->dispatch(new Request('GET', '/router-dynamic-fixture/missing'));
    assertSame(404, $missingMethod->status(), 'Olmayan explicit method index fallback ile calismamali.');

    $hiddenMethod = $router->dispatch(new Request('GET', '/router-dynamic-fixture/hidden'));
    assertSame(404, $hiddenMethod->status(), 'Protected method dinamik route ile cagrilmamali.');

    $missingArgument = $router->dispatch(new Request('GET', '/router-dynamic-fixture/show'));
    assertSame(404, $missingArgument->status(), 'Eksik URL parametresi 500 yerine 404 donmeli.');

    $validShow = $router->dispatch(new Request('GET', '/router-dynamic-fixture/show/42'));
    assertSame(200, $validShow->status(), 'Gecerli dinamik method calismali.');
    assertSame('show:42', $validShow->body(), 'Dinamik method dogru parametreyi almali.');

    echo "RouterDynamicDispatchTest ok\n";
}
