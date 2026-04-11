<?php

use Illuminate\Support\Facades\Route;

// Віддає Vue SPA для всіх веб маршрутів
Route::get('/{any?}', function () {
    $indexPath = public_path('frontend/index.html');
    
    if (file_exists($indexPath)) {
        return response()->file($indexPath);
    }
    
    return response('Frontend not built yet', 404);
})->where('any', '.*');
