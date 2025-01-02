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
use TallStackUi\Foundation\TallStackUI;  // Updated to use the correct Foundation class

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
        try {
            // Core Laravel configurations that should run first
            $this->configureUrl();
            $this->configureStrictMode();
            
            // Database-independent package configurations
            $this->configureOptionalPackages();
            
            // Database-dependent configurations run last
            if ($this->isDatabaseOnline() && Schema::hasTable('settings')) {
                $this->configureDatabaseDependentServices();
            }
            
            $this->addAboutCommandDetails();
        } catch (\Exception $e) {
            // Log the error but allow the application to continue booting
            Log::error('AppServiceProvider boot failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Configure optional packages with proper error handling.
     * Each package configuration is isolated in its own try-catch block
     * to prevent one package's failure from affecting others.
     */
    private function configureOptionalPackages(): void
    {
        // Configure TallStackUI with proper error boundaries
        $this->configureTallStackUiPersonalization();

        // Configure LogViewer with its own error handling
        try {
            if (class_exists('\Opcodes\LogViewer\Facades\LogViewer')) {
                $this->configureLogViewer();
            }
        } catch (\Exception $e) {
            Log::warning('LogViewer configuration failed: ' . $e->getMessage());
        }
    }

    /**
     * Configure TallStackUI personalization using the v1.37.1 API.
     * Each component's personalization is handled separately for better error isolation.
     */
    private function configureTallStackUiPersonalization(): void
    {
        try {
            // Get the personalization instance through Laravel's container
            $personalize = app(TallStackUI::class)->personalize();

            // Configure card component
            $personalize->card()
                ->block('wrapper.second')
                ->replace('rounded-lg', 'rounded')
                ->block('wrapper.second')
                ->replace('dark:bg-dark-700', 'dark:bg-neutral-700');

            // Configure input component
            $personalize->input()
                ->block('input.wrapper')
                ->replace('rounded-md', 'rounded')
                ->block('input.base')
                ->replace('rounded-md', 'rounded');

            // Configure modal component
            $personalize->modal()
                ->block('wrapper.fourth')
                ->replace('rounded-xl', 'rounded')
                ->block('wrapper.fourth')
                ->replace('dark:bg-dark-700', 'dark:bg-dark-900');

        } catch (\Exception $e) {
            // Provide detailed error logging for debugging in production
            Log::warning('TallStackUI personalization failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'class_exists' => class_exists(TallStackUI::class),
                'container_binding' => app()->bound('tallstackui'),
            ]);
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