<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class MaintenanceController extends Controller
{
    public const COMMANDS = [
        'migrate' => [
            'name' => 'Database Migration',
            'command' => 'php artisan migrate --force',
            'artisan' => 'migrate',
            'params' => ['--force' => true],
            'description' => 'Executes all pending database migrations.',
            'icon' => 'bi-database-fill-gear',
            'color' => 'accent',
            'badge' => 'Database',
        ],
        'optimize_clear' => [
            'name' => 'Optimize Clear',
            'command' => 'php artisan optimize:clear',
            'artisan' => 'optimize:clear',
            'params' => [],
            'description' => 'Clears all compiled cached files (cache, routes, config, views).',
            'icon' => 'bi-stars',
            'color' => 'success',
            'badge' => 'All in One',
        ],
        'cache_clear' => [
            'name' => 'Application Cache',
            'command' => 'php artisan cache:clear',
            'artisan' => 'cache:clear',
            'params' => [],
            'description' => 'Flushes the application data cache.',
            'icon' => 'bi-hdd-stack',
            'color' => 'info',
            'badge' => 'Cache',
        ],
        'config_clear' => [
            'name' => 'Configuration Cache',
            'command' => 'php artisan config:clear',
            'artisan' => 'config:clear',
            'params' => [],
            'description' => 'Clears the cached configuration file so updates take effect.',
            'icon' => 'bi-gear-wide',
            'color' => 'warning',
            'badge' => 'Config',
        ],
        'route_clear' => [
            'name' => 'Route Cache',
            'command' => 'php artisan route:clear',
            'artisan' => 'route:clear',
            'params' => [],
            'description' => 'Flushes the route registration cache.',
            'icon' => 'bi-signpost-split',
            'color' => 'primary',
            'badge' => 'Routes',
        ],
        'view_clear' => [
            'name' => 'Compiled Views Cache',
            'command' => 'php artisan view:clear',
            'artisan' => 'view:clear',
            'params' => [],
            'description' => 'Clears all compiled Blade template views.',
            'icon' => 'bi-file-earmark-code',
            'color' => 'danger',
            'badge' => 'Views',
        ],
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (! $user) {
                abort(403);
            }
            if ($user->hasRole('admin') || $user->can('view maintenance') || $user->can('run maintenance')) {
                return $next($request);
            }
            abort(403, 'Unauthorized access.');
        });
    }

    public function index(): View
    {
        $commands = self::COMMANDS;
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug') ? 'Enabled' : 'Disabled',
            'db_connection' => config('database.default'),
            'cache_driver' => config('cache.default'),
        ];

        return view('admin.maintenance.index', compact('commands', 'systemInfo'));
    }

    public function run(Request $request): RedirectResponse
    {
        $action = (string) $request->input('command');
        abort_unless(isset(self::COMMANDS[$action]), 404);

        $commandConfig = self::COMMANDS[$action];

        try {
            $exitCode = Artisan::call($commandConfig['artisan'], $commandConfig['params'] ?? []);
            $output = trim(Artisan::output());

            if ($action === 'migrate') {
                if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
                    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
                }
            }

            if ($output === '') {
                $output = "Command {$commandConfig['command']} executed successfully with exit code {$exitCode}.";
            }

            return back()
                ->with('success', "{$commandConfig['name']} ({$commandConfig['command']}) executed successfully.")
                ->with('artisan_output', $output)
                ->with('artisan_command', $commandConfig['command']);
        } catch (Throwable $e) {
            return back()
                ->with('error', "Failed to execute {$commandConfig['command']}: {$e->getMessage()}")
                ->with('artisan_output', $e->getMessage())
                ->with('artisan_command', $commandConfig['command']);
        }
    }
}
