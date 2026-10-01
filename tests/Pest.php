<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Pest\Browser\Playwright\Servers\PlaywrightNpmServer;
use Pest\Browser\Plugin as BrowserPlugin;
use Pest\Browser\ServerManager;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Browser');

/*
|--------------------------------------------------------------------------
| Playwright Cleanup
|--------------------------------------------------------------------------
|
| pest-plugin-browser stops the "sh -c" wrapper of its Playwright server but leaves the
| "node playwright run-server" child alive. When this run started a server, kill that
| child by its port, so servers of parallel runs or other projects are left untouched.
| SIGKILL is required: the orphan ignores SIGTERM at this point, and the plugin has
| already closed the browser, so no Chromium process is left behind. If the plugin's
| internal API changes, the hook only prints a warning and never fails the test run.
|
*/

register_shutdown_function(function (): void {
    $disable = function (): void {
        fwrite(STDERR, 'Playwright cleanup disabled: the internal API of pest-plugin-browser changed. Update the hook in tests/Pest.php.'.PHP_EOL);
    };

    // ponytail: relies on the plugin's @internal classes; remove once the plugin kills its own child process.
    if (! class_exists(BrowserPlugin::class) || ! property_exists(BrowserPlugin::class, 'booted')
        || ! class_exists(ServerManager::class) || ! class_exists(PlaywrightNpmServer::class)) {
        $disable();

        return;
    }

    try {
        if (! BrowserPlugin::$booted) {
            return;
        }

        $server = ServerManager::instance()->playwright();

        if (! $server instanceof PlaywrightNpmServer) {
            $disable();

            return;
        }

        $pattern = sprintf('^node .*playwright run-server --host %s --port %d ', preg_quote($server->host), $server->port);

        exec('pkill -KILL -f '.escapeshellarg($pattern));
    } catch (Throwable) {
        $disable();
    }
});

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
