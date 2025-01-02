<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;
use TallStackUi\Facades\TallStackUi;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     * This method orchestrates the initialization of all application services and packages.
     * It follows a specific order to ensure dependencies are properly handled.
     */
    public function boot(): void
    {
        // Core Laravel configurations that don't depend on external packages
        $this->configureUrl();
        $this->configureStrictMode();
        $this->addAboutCommandDetails();

        // Configure optional packages with error handling
        $this->configureOptionalPackages();

        // Database-dependent configurations only run if database is accessible
        if ($this->isDatabaseOnline() && Schema::hasTable('settings')) {
            $this->configureDatabaseDependentServices();
        }
    }

    /**
     * Configure optional packages with proper error handling.
     * Each package configuration is isolated in its own try-catch block
     * to prevent one package's failure from affecting others.
     */
    private function configureOptionalPackages(): void
    {
        // TallStackUI configuration needs to run first as it sets up UI components
        try {
            if (class_exists('\TallStackUi\Facades\TallStackUi')) {
                $this->configureTallStackUiPersonalization();
            }
        } catch (\Exception $e) {
            Log::warning('TallStackUI configuration failed: ' . $e->getMessage());
        }

        // LogViewer configuration runs after UI setup is complete
        try {
            if (class_exists('\Opcodes\LogViewer\Facades\LogViewer')) {
                $this->configureLogViewer();
            }
        } catch (\Exception $e) {
            Log::warning('LogViewer configuration failed: ' . $e->getMessage());
        }
    }

/**
 * Configure TallStackUI personalization with component-specific settings.
 * This method uses TallStackUI's personalization API to customize components.
 */
private function configureTallStackUiPersonalization(): void
{
    try {
        // Get the personalization instance from TallStackUI
        $ui = app('tallstackui')->personalize();

        // Now we can configure each component using the personalization instance
        
        // Alert Component: Adjust wrapper styles for a more subtle appearance
        $ui->alert()
            ->block('wrapper')
            ->replace('rounded-lg', 'rounded');

        // Badge Component: Fine-tune the padding for better visual balance
        $ui->badge()
            ->block('wrapper.class')
            ->replace('px-2', 'px-1');

        // Card Component: Create a cohesive card design with consistent spacing and colors
        $ui->card()
            ->block('wrapper.first')->replace('gap-4', 'gap-2')
            ->block('wrapper.second')->replace('rounded-lg', 'rounded')
            ->block('wrapper.second')->replace('dark:bg-dark-700', 'dark:bg-neutral-700')
            ->block('header.wrapper', 'dark:border-b-neutral-600 flex items-center justify-between border-b border-b-gray-100 p-2')
            ->block('footer.wrapper', 'text-secondary-700 dark:text-dark-300 dark:border-t-neutral-600 rounded rounded-t-none border-t p-2')
            ->block('footer.text', 'flex items-center justify-end gap-2');

        // Dropdown Component: Enhance usability with improved spacing and visual hierarchy
        $ui->dropdown()
            ->block('floating')->replace('rounded-lg', 'rounded')
            ->block('width')->replace('w-56', 'w-64')
            ->block('action.icon')->replace('text-gray-400', 'text-primary-500 dark:text-primary-300');

        // Form Components: Create a consistent form element appearance
        $ui->form('input')
            ->block('input.wrapper')->replace('rounded-md', 'rounded')
            ->block('input.base')->replace('rounded-md', 'rounded');

        $ui->form('textarea')
            ->block('input.wrapper')->replace('rounded-md', 'rounded')
            ->block('input.base')->replace('rounded-md', 'rounded');

        $ui->form('label')
            ->block('text')->replace('text-gray-600', 'text-gray-700')
            ->block('text')->replace('dark:text-dark-400', 'dark:text-dark-500');

        // Modal Component: Improve modal appearance with subtle background and proper spacing
        $ui->modal()
            ->block('wrapper.first')->replace('bg-opacity-50', 'bg-opacity-20')
            ->block('wrapper.fourth')->replace('dark:bg-dark-700', 'dark:bg-dark-900')
            ->block('wrapper.fourth')->replace('rounded-xl', 'rounded');

        // Slide Component: Enhance slide transitions and visual consistency
        $ui->slide()
            ->block('wrapper.first')->replace('bg-opacity-50', 'bg-opacity-20')
            ->block('wrapper.fifth')->replace('dark:bg-dark-700', 'dark:bg-dark-900')
            ->block('footer')->append('dark:text-secondary-600');

        // Tab Component: Improve tab navigation visibility and spacing
        $ui->tab()
            ->block('base.wrapper')->replace('rounded-lg', 'rounded')
            ->block('base.wrapper')->replace('dark:bg-dark-700', 'dark:bg-neutral-700')
            ->block('item.select')->replace('dark:text-dark-300', 'dark:text-neutral-50');

        // Table Component: Optimize table spacing and appearance
        $ui->table()
            ->block('wrapper')->replace('rounded-lg', 'rounded')
            ->block('table.td')->replace('py-4', 'py-2');

    } catch (\Exception $e) {
        // Log any configuration errors for debugging
        Log::warning('TallStackUI personalization failed: ' . $e->getMessage());
    }
}

    /**
     * Configure LogViewer settings and access control.
     * Only developers are granted access to the log viewer in production.
     */
    private function configureLogViewer(): void
    {
        if (!config('log-viewer.enabled', false)) {
            return;
        }

        LogViewer::auth(function ($request) {
            return $request->user()->is_developer;
        });
    }

    /**
     * Enforce HTTPS in production environment.
     */
    private function configureUrl(): void
    {
        URL::forceHttps(app()->isProduction());
    }

    /**
     * Configure strict mode for model behavior.
     * Enables stricter validation in development environments.
     */
    private function configureStrictMode(): void
    {
        Model::shouldBeStrict(!app()->isProduction());
    }

    /**
     * Add application details to the About command.
     */
    private function addAboutCommandDetails(): void
    {
        AboutCommand::add('Application', [
            'Name'    => 'Genealogy',
            'Author'  => 'kreaweb.be',
            'GitHub'  => 'https://github.com/MGeurts/genealogy',
            'License' => 'MIT License',
        ]);
    }

    /**
     * Configure database-dependent services including settings and logging.
     */
    private function configureDatabaseDependentServices(): void
    {
        // Cache the applications settings
        $this->app->singleton('settings', function () {
            return Cache::rememberForever('settings', function () {
                return Setting::all()->pluck('value', 'key');
            });
        });

        // Enable or disable logging based on application settings
        $this->logAllQueries();
        $this->LogAllQueriesSlow();
        $this->logAllQueriesNplusone();
    }

    /**
     * Log all queries for debugging purposes if enabled in settings.
     */
    private function logAllQueries(): void
    {
        if (settings('log_all_queries')) {
            DB::listen(fn ($query) => Log::debug($query->toRawSQL()));
        }
    }

    /**
     * Log slow queries based on configured threshold.
     */
    private function LogAllQueriesSlow(): void
    {
        if (settings('log_all_queries_slow')) {
            DB::listen(function ($query) {
                if ($query->time >= settings('log_all_queries_slow_threshold')) {
                    Log::warning('An individual database query exceeded ' . settings('log_all_queries_slow_threshold') . ' ms.', [
                        'sql' => $query->sql,
                        'raw' => $query->toRawSQL(),
                    ]);
                }
            });
        }
    }

    /**
     * Log N+1 query violations for debugging purposes.
     */
    private function logAllQueriesNplusone(): void
    {
        if (settings('log_all_queries_n+1')) {
            Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
                Log::warning(sprintf(
                    'N+1 Query detected in model %s on relation %s.',
                    get_class($model),
                    $relation
                ));
            });
        }
    }

    /**
     * Check if the database connection is available.
     */
    protected function isDatabaseOnline(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}