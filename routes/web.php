<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan; /* Added for Web UI */

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*  */
Route::get('/sync', function () {
    return view('sync');
});

Route::post('/sync', function (\Illuminate\Http\Request $request) {
    $token = trim((string) $request->input('token', ''));
    $expected = trim((string) env('SYNC_TOKEN', ''));

    abort_unless(
        $token !== '' && $expected !== '' && hash_equals($expected, $token),
        403
    );

    $dryRun = (bool) $request->input('dry_run');

    $args = [];
    if ($dryRun) {
       $args['--dryrun'] = true;
    }

    Artisan::call('things:sync', $args);

    $output = Artisan::output();

    return view('sync', [
        'output' => $output,
        'token' => $token,
        'dry_run' => $dryRun,
    ]);
});
