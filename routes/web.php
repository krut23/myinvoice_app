<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PdfController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/storage-link', function () {
//   $targetFolder = storage_path('app/public');
//   $linkFolder = $_SERVER['DOCUMENT_ROOT'] . '/storage';
//   symlink( $targetFolder, $linkFolder);
// });

Route::get('/symlink', function () {
    Artisan::call('storage:link');
    echo "Done";
});

Route::middleware('jwt')->group(function () {

 Route::post('/invoice',[PdfController::class, 'invoiceGenerate'])->name('invoice.icons');

});
Route::get('/download-pdf/{final_id}', [PdfController::class, 'downloadPDF'])->name('invoice.download');