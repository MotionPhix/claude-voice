<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file contains the main web routes for the application.
| Role-specific routes are organized in separate files for better
| maintainability and clear permission boundaries.
|
*/

Route::get('/', function () {
    return to_route('dashboard');
})->name('home');

/*
|--------------------------------------------------------------------------
| Role-based Route Includes
|--------------------------------------------------------------------------
|
| Routes are organized by access level and functionality:
| - shared.php: Routes available to all authenticated users
| - manager.php: Business operations (Manager+ level)
| - accountant.php: Financial operations (Accountant+ level)
| - admin.php: Administrative functions (Admin+ level)
|
*/

require __DIR__.'/shared.php';      // All authenticated users
require __DIR__.'/manager.php';     // Manager level and above
require __DIR__.'/accountant.php';  // Accountant level and above
require __DIR__.'/admin.php';       // Admin level and above

/*
|--------------------------------------------------------------------------
| Authentication & Settings Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/api.php';
