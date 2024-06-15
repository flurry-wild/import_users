<?php

namespace App\Providers;

use App\Classes\Maatwebsite\CustomReader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Cache\CacheManager;
use Maatwebsite\Excel\Console\ExportMakeCommand;
use Maatwebsite\Excel\Console\ImportMakeCommand;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Exporter;
use Maatwebsite\Excel\Files\Filesystem;
use Maatwebsite\Excel\Files\TemporaryFileFactory;
use Maatwebsite\Excel\Importer;
use Maatwebsite\Excel\Mixins\DownloadCollectionMixin;
use Maatwebsite\Excel\Mixins\DownloadQueryMacro;
use Maatwebsite\Excel\Mixins\ImportAsMacro;
use Maatwebsite\Excel\Mixins\ImportMacro;
use Maatwebsite\Excel\Mixins\StoreCollectionMixin;
use Maatwebsite\Excel\Mixins\StoreQueryMacro;
use Maatwebsite\Excel\QueuedWriter;
use Maatwebsite\Excel\SettingsProvider;
use Maatwebsite\Excel\Transactions\TransactionHandler;
use Maatwebsite\Excel\Transactions\TransactionManager;
use Maatwebsite\Excel\Writer;

class ExcelServiceProvider extends \Maatwebsite\Excel\ExcelServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Console/stubs/export.model.stub'       => base_path('stubs/export.model.stub'),
                __DIR__ . '/Console/stubs/export.plain.stub'       => base_path('stubs/export.plain.stub'),
                __DIR__ . '/Console/stubs/export.query.stub'       => base_path('stubs/export.query.stub'),
                __DIR__ . '/Console/stubs/export.query-model.stub' => base_path('stubs/export.query-model.stub'),
                __DIR__ . '/Console/stubs/import.collection.stub'  => base_path('stubs/import.collection.stub'),
                __DIR__ . '/Console/stubs/import.model.stub'       => base_path('stubs/import.model.stub'),
            ], 'stubs');

            $this->publishes([
                $this->getConfigFile() => config_path('excel.php'),
            ], 'config');
        }

        // Laravel
        $this->app->booted(function ($app) {
            $app->make(SettingsProvider::class)->provide();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(
            $this->getConfigFile(),
            'excel'
        );

        $this->app->bind(CacheManager::class, function ($app) {
            return new CacheManager($app);
        });

        $this->app->singleton(TransactionManager::class, function ($app) {
            return new TransactionManager($app);
        });

        $this->app->bind(TransactionHandler::class, function ($app) {
            return $app->make(TransactionManager::class)->driver();
        });

        $this->app->bind(TemporaryFileFactory::class, function () {
            return new TemporaryFileFactory(
                config('excel.temporary_files.local_path', config('excel.exports.temp_path', storage_path('framework/laravel-excel'))),
                config('excel.temporary_files.remote_disk')
            );
        });

        $this->app->bind(Filesystem::class, function ($app) {
            return new Filesystem($app->make('filesystem'));
        });

        $this->app->bind(CustomReader::class, function ($app) {
            return new CustomReader(
                $app->make(TemporaryFileFactory::class),
                $app->make(TransactionHandler::class),
            );
        });

        $this->app->bind('excel', function ($app) {
            return new Excel(
                $app->make(Writer::class),
                $app->make(QueuedWriter::class),
                $app->make(CustomReader::class),
                $app->make(Filesystem::class)
            );
        });

        $this->app->alias('excel', Excel::class);
        $this->app->alias('excel', Exporter::class);
        $this->app->alias('excel', Importer::class);

        Collection::mixin(new DownloadCollectionMixin);
        Collection::mixin(new StoreCollectionMixin);
        Builder::macro('downloadExcel', (new DownloadQueryMacro)());
        Builder::macro('storeExcel', (new StoreQueryMacro())());
        Builder::macro('import', (new ImportMacro())());
        Builder::macro('importAs', (new ImportAsMacro())());

        $this->commands([
            ExportMakeCommand::class,
            ImportMakeCommand::class,
        ]);
    }
}
