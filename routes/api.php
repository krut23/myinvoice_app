<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\TaxController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PartyController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\AddItemController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ItemDetailsController;
use App\Http\Controllers\Api\TempInvoiceController;
use App\Http\Controllers\Api\InvoiceItemDataController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// User Controller Route
Route::post('/register', [UserController::class, 'user_register']);
Route::post('/login', [LoginController::class, 'login'])->withoutMiddleware(['jwt']);


Route::middleware('jwt')->group(function () {

// User Controller Route
    Route::get('show', [UserController::class, 'businessname_phonenumber']);
    Route::post('user-check-username', [UserController::class, 'check_username_register_or_not']);
    Route::post('users-userName', [UserController::class, 'fetch_user_name']);
    Route::post('users-password', [UserController::class, 'fetch_user_password']);
    Route::post('users-gst', [UserController::class, 'update_gst_registration']);
    Route::get('users-ph-gst', [UserController::class, 'show_ph_gst_address']);
    Route::post('user-update-signature', [UserController::class, 'add_signature']);
    Route::post('user_update', [UserController::class, 'user_update']);

    Route::post('update_business',[UserController::class,'update_business_user']);
    Route::post('update_number',[UserController::class,'update_phone_number']);
    Route::post('update_name',[UserController::class,'update_user_name']);
    Route::get('userData', [UserController::class, 'user_add_sign_logo_ph']);
    Route::get('get_logo',[UserController::class,'user_name_logo']);
    Route::get('view_signature',[UserController::class,'view_signature']);
    Route::post('delete_signature', [UserController::class, 'deleteSignature']);



// Login Controller Route
    Route::post('update-password', [LoginController::class, 'updatePassword']);
    Route::get('logout', [LoginController::class, 'logout']);
    Route::delete('delete', [LoginController::class, 'delete']);

// Category Controller Route
    Route::post('categories', [CategoryController::class, 'createCategory']);
    Route::post('delete-category', [CategoryController::class, 'delete_category_all_data']);
    Route::post('delete-categoryById', [CategoryController::class, 'delete_category']);

    Route::post('category_user',[CategoryController::class,'select_category_name']);
    Route::post('category_update',[CategoryController::class,'update_category']);
    Route::post('Category_name',[CategoryController::class, 'view_category']);

// Item Controller Route
    Route::post('add-item', [AddItemController::class, 'add_item']);
    Route::post('items-update-extra-qty', [AddItemController::class, 'updateExtraQtyByUserId']);
    Route::post('delete-item-data', [AddItemController::class, 'delete_item_all_data']);
    Route::post('delete-item', [AddItemController::class, 'delete_item']);
    Route::post('show_item_details', [AddItemController::class, 'Show_item_details']);

    Route::post('StockData',[AddItemController::class, 'select_opening_stock']);
    Route::post('get_date_item',[AddItemController::class,'item_date_stock']);
    Route::post('ext_qty',[AddItemController::class,'update_extra_qty']);
    Route::post('updated-stock', [AddItemController::class, 'update_stock']);  //
    Route::post('item_update',[AddItemController::class,'update_item']);  //
    Route::post('fieldList',[AddItemController::class,'view_item']);





// Invoice Controller Route
    Route::post('createInvoice', [InvoiceController::class, 'createInvoice']);
    Route::post('invoice', [InvoiceController::class, 'add_received_showinvoice']);
    Route::post('receive-update-invoice', [InvoiceController::class, 'add_received_update']);
    Route::post('invoice-allData', [InvoiceController::class, 'delete_invoice_all_data_ByUserId']);
    Route::post('delete-invoice', [InvoiceController::class, 'delete_invoice']);
    Route::post('invoices-customer-name', [InvoiceController::class, 'find_customer_name']);
    Route::post('find-week-dates', [InvoiceController::class, 'find_week_dates']);
    Route::post('week_dates', [InvoiceController::class, 'this_week_date']);
    
        Route::post('show_week_invoice', [InvoiceController::class, 'show_week_invoice']);


    Route::get('collect_amount',[InvoiceController::class,'to_collect_amount']);
    Route::get('amoutDetails',[InvoiceController::class,'to_collect_amunt_details']);
    Route::post('userBalance',[InvoiceController::class,'transaction_balance_amt']);
    Route::post('invoice', [InvoiceController::class, 'update_invoice']);
    Route::get('bill_invoice',[InvoiceController::class,'view_bill']);
    Route::post('invoice_details',[InvoiceController::class,'view_invoice_details']);
    Route::get('bill_balance_amount',[InvoiceController::class,'view_bill_balance_amount']);






// Invoice_Item_Data Controller Route
    Route::post('invoice-item-data-allData', [InvoiceItemDataController::class, 'delete_invoice_item_data_all_data']);
    Route::post('invoice-item-data', [InvoiceItemDataController::class, 'delete_invoice_item_data_ByFinalId']);
    Route::post('invoice-item', [InvoiceItemDataController::class, 'invc_item_table']);
    Route::get('invoice-copy', [InvoiceItemDataController::class, 'item_copy_data']);

    Route::post('id_update',[InvoiceItemDataController::class,'update_final_id']);



// Item_Details Controller Route
    Route::post('add-item-details', [ItemDetailsController::class, 'add_item_details']);
    Route::post('delete-item-details-all-data', [ItemDetailsController::class, 'delete_item_details_all_data_ByUserId']);
    Route::post('delete-item-details', [ItemDetailsController::class, 'delete_item_details_ByItemId']);

    Route::post('item_details',[ItemDetailsController::class,'view_item_timeline']);


// Party Controller Route
    Route::post('party', [PartyController::class, 'createParty']);
    Route::post('show-party', [PartyController::class, 'show_partyName_unique']);
    Route::post('delete-party-all-data', [PartyController::class, 'delete_party_all_data']);
    Route::post('parties', [PartyController::class, 'edit_party']);
    Route::post('party_details', [PartyController::class, 'show_party_details']);

    Route::post('delete_party',[PartyController::class,'delete_party']);
    Route::get('party_name',[PartyController::class,'view_party']);
    Route::get('to_receive',[PartyController::class,'view_balance']);




// Tax Controller
    Route::post('TotalTax',[TaxController::class,'total_tax_total_qty']);

// Temp Invoice Controller
    Route::post('add_data_temp',[TempInvoiceController::class,'temp_add_item_invoice']);
    Route::post('delete_data_temp',[TempInvoiceController::class,'temp_delete_invoice']);
    Route::post('get_total_items',[TempInvoiceController::class,'temp_item_count']);
    Route::post('get_total_qty',[TempInvoiceController::class,'temp_total_qty']);
    Route::post('get_sales_price',[TempInvoiceController::class,'temp_total']);

    Route::post('update_tempDetails',[TempInvoiceController::class,'temp_update_item_details']);
    Route::post('unvisible_stock',[TempInvoiceController::class,'unvisible_stock_item']); // same  temp_update_item_details
    Route::get('get_temp_invoice',[TempInvoiceController::class,'show_temp_invoice']);
    Route::post('get_temp_qty',[TempInvoiceController::class,'visible']);
    Route::post('get_temp_item',[TempInvoiceController::class,'view_temp_item']);

    Route::post('/invoice',[PdfController::class, 'invoiceGenerate'])->name('invoice.icons');
Route::get('/download-pdf/{final_id}', [PdfController::class, 'downloadPDF'])->name('invoice.download');

Route::post('show_pdf_url', [PdfController::class, 'show_pdf_url']);
});
