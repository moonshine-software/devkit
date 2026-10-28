<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Illuminate\Http\Request;
use Laravel\Octane\ApplicationFactory;
use Laravel\Octane\RequestContext;
use Laravel\Octane\Testing\Fakes\FakeClient;
use Laravel\Octane\Testing\Fakes\FakeWorker;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

/**
 * Serves admin pages repeatedly through a real Octane worker loop (a sandbox clone of one booted
 * application per request, Octane's state-resetting listeners, RequestHandled) and checks that
 * memory stays flat and no request state leaks into the next request.
 */
#[Group('octane')]
final class OctaneMemoryTest extends TestCase
{
    private const array PAGES = [
        'dashboard' => '/admin',
        'posts index' => '/admin/resource/post-resource/post-index-page',
        'posts async table' => '/admin/component/post-index-page/post-resource?_component_name=index-table-post-resource&p_reset=1&p_sort=id',
        'post form' => '/admin/resource/post-resource/post-form-page/1',
        'post detail' => '/admin/resource/post-resource/post-detail-page/1',
        'comment detail' => '/admin/resource/comment-resource/comment-detail-page/1',
        'admins' => '/admin/resource/moon-shine-user-resource/moon-shine-user-index-page',
        'components' => '/admin/page/components',
    ];

    private FakeWorker $worker;

    private FakeClient $client;

    /** @var array<string, string> */
    private array $cookies = [];

    protected function setUp(): void
    {
        $basePath = \dirname(__DIR__, 2);
        $database = $basePath . '/database/octane-memory.sqlite';

        // A separate database and the HTTP runtime settings of a real Octane worker.
        $env = [
            'APP_ENV' => 'local',
            'APP_RUNNING_IN_CONSOLE' => 'false',
            'LARAVEL_OCTANE' => '1',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $database,
            'SESSION_DRIVER' => 'file',
            'CACHE_STORE' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'DEBUGBAR_ENABLED' => 'false',
        ];

        foreach ($env as $key => $value) {
            putenv("$key=$value");
            $_ENV[$key] = $_SERVER[$key] = $value;
        }

        touch($database);

        foreach (['migrate:fresh --seed --force', 'db:seed --class=E2eSeeder --force'] as $command) {
            $process = new Process([\PHP_BINARY, 'artisan', ...explode(' ', $command)], $basePath, [...$env, 'APP_RUNNING_IN_CONSOLE' => 'true'], timeout: 300);
            $process->mustRun();
        }

        $this->client = new FakeClient([]);
        $this->worker = new FakeWorker(new ApplicationFactory($basePath), $this->client);
        $this->worker->boot();

        $this->login();
    }

    protected function tearDown(): void
    {
        // The booted application registers Laravel's error and exception handlers.
        HandleExceptions::flushState($this);

        parent::tearDown();
    }

    public function test_memory_stays_flat_across_requests(): void
    {
        // Without OPcache every include of a compiled Blade view allocates new opcodes, which looks like a leak.
        if (! filter_var(\ini_get('opcache.enable_cli'), FILTER_VALIDATE_BOOL)) {
            $this->markTestSkipped('Run with OPcache, as Octane does: composer test:octane');
        }

        $warmup = (int) (getenv('OCTANE_MEMORY_WARMUP') ?: 10);
        $rounds = (int) (getenv('OCTANE_MEMORY_ROUNDS') ?: 40);
        $limit = (int) (getenv('OCTANE_MEMORY_LIMIT_KB') ?: 512);

        $usage = [];

        for ($round = 0; $round < $warmup + $rounds; $round++) {
            foreach (self::PAGES as $name => $url) {
                $response = $this->get($url, xhr: str_contains($url, '_component_name'));

                $this->assertSame(200, $response->getStatusCode(), "$name in round $round");
            }

            gc_collect_cycles();
            $usage[] = memory_get_usage();
        }

        $measured = \array_slice($usage, $warmup);
        $growth = (int) round((end($measured) - $measured[0]) / 1024);
        $requests = $rounds * \count(self::PAGES);

        fwrite(STDERR, \sprintf(
            "\nOctane memory: %.1f MB after warm-up, %+d KB after %d more requests (limit %d KB)\nPer round, KB: %s\n",
            $measured[0] / 1048576,
            $growth,
            $requests,
            $limit,
            implode(' ', array_map(static fn (int $bytes): int => (int) round(($bytes - $measured[0]) / 1024), $measured)),
        ));

        $this->assertLessThanOrEqual($limit, $growth, "Memory grew by $growth KB over $requests requests");
    }

    public function test_request_state_does_not_leak_into_the_next_request(): void
    {
        $url = '/admin/component/post-index-page/post-resource?_component_name=index-table-post-resource&p_reset=1';

        $ascending = $this->firstRowKey($this->get("$url&p_sort=id", xhr: true));
        $descending = $this->firstRowKey($this->get("$url&p_sort=-id", xhr: true));

        $this->assertNotSame($ascending, $descending, 'The second request rendered the sort order of the first one');
        $this->assertSame($ascending, $this->firstRowKey($this->get("$url&p_sort=id", xhr: true)));

        // A request without the admin session must not reuse the previous request's user.
        $cookies = $this->cookies;
        $this->cookies = [];

        $this->assertSame(302, $this->get('/admin')->getStatusCode());

        $this->cookies = $cookies;
        $this->assertSame(200, $this->get('/admin')->getStatusCode());
    }


    private function login(): void
    {
        $form = $this->get('/admin/login');

        preg_match('/name="_token" value="([^"]+)"/', (string) $form->getContent(), $token);
        $this->assertNotEmpty($token, 'The login form has no CSRF token');

        $response = $this->request('POST', '/admin/authenticate', [
            '_token' => $token[1],
            'username' => getenv('E2E_USERNAME') ?: 'dev@getmoonshine.app',
            'password' => getenv('E2E_PASSWORD') ?: '12345',
        ]);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringNotContainsString('/login', (string) $response->headers->get('Location'));
    }

    private function get(string $url, bool $xhr = false): Response
    {
        return $this->request('GET', $url, server: $xhr ? ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'] : []);
    }

    /**
     * @param  array<string, string>  $data
     * @param  array<string, string>  $server
     */
    private function request(string $method, string $url, array $data = [], array $server = []): Response
    {
        $request = Request::create('http://127.0.0.1' . $url, $method, $data, $this->cookies, server: ['HTTP_ACCEPT' => 'text/html', ...$server]);

        $this->worker->handle($request, new RequestContext(['request' => $request]));

        $this->assertSame([], $this->client->errors);

        /** @var Response $response */
        $response = array_pop($this->client->responses);

        foreach ($response->headers->getCookies() as $cookie) {
            $this->cookies[$cookie->getName()] = (string) $cookie->getValue();
        }

        return $response;
    }

    private function firstRowKey(Response $response): string
    {
        preg_match('/data-row-key="([^"]+)"/', (string) $response->getContent(), $key);
        $this->assertNotEmpty($key, 'The table has no rows');

        return $key[1];
    }
}
