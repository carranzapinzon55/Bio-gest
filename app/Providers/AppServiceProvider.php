<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Producto;

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
     */
    public function boot(): void
    {
        // Compartir conteo de alertas de stock bajo con todas las vistas administrativas
        View::composer(['layouts.admin', 'layouts.app', 'admin.*'], function ($view) {
            try {
                $conteoAlertasStock = Producto::whereColumn('stock', '<=', 'stock_minimo')->count();
            } catch (\Throwable $e) {
                $conteoAlertasStock = 0;
            }
            $view->with('conteoAlertasStock', $conteoAlertasStock);
        });
    }
}
