<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class ObfuscatorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $compiler) {
            $compiler->directive('obfuscate', function ($expression) {
                return "<?php echo app('App\Services\JsObfuscator')->obfuscate({$expression}); ?>";
            });
        });
    }
}
