<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

// User Controller Route //13
    Route::get('show', [UserController::class, 'businessname_phonenumber']);
    Route::get('user-check-username', [UserController::class, 'check_username_register_or_not']);
    Route::post('users-userName', [UserController::class, 'fetch_user_name']);
    Route::post('users-password', [UserController::class, 'fetch_user_password']);
    Route::put('users', [UserController::class, 'update_gst_registration']);
    Route::post('users', [UserController::class, 'show_ph_gst_address']); //checked
    Route::post('user-update-signature', [UserController::class, 'add_signature']);

    Route::post('user_update',[UserController::class,'update_business_user']); //checked
    Route::post('update_number',[UserController::class,'update_phone_number']); //checked
    Route::post('update_name',[UserController::class,'update_your_name']);  //checked
    Route::get('userData', [UserController::class, 'user_add_sign_logo_ph']);  //checked
    Route::post('get_logo',[UserController::class,'user_name_logo']); //checked
    Route::get('signature',[UserController::class,'view_signature']);  //checkd



// Login Controller Route //1
    Route::put('users-password', [LoginController::class, 'updatePassword']);
    Route::post('logout', [LoginController::class, 'logout']);


// Category Controller Route  //6
    Route::post('categories', [CategoryController::class, 'createCategory']);
    Route::post('delete-category', [CategoryController::class, 'delete_category_all_data']);
    Route::post('delete-categoryById', [CategoryController::class, 'delete_category']);

    Route::get('category_user',[CategoryController::class,'select_category_name']); //checked
    Route::post('category_update',[CategoryController::class,'update_category']); //checked
    Route::get('Category_name',[CategoryController::class, 'view_category']); //checked

// Item Controller Route //11
    Route::post('add-item', [AddItemController::class, 'addItem']);
    Route::post('items-update-extra-qty', [AddItemController::class, 'updateExtraQtyByUserId']);
    Route::post('delete-item-data', [AddItemController::class, 'delete_item_all_data']);
    Route::post('delete-item', [AddItemController::class, 'delete_item']);
    Route::post('show_item_details', [AddItemController::class, 'Show_item_details']); //checked

    Route::get('StockData',[AddItemController::class, 'select_opening_stock']); //checked
    Route::get('get_date_item',[AddItemController::class,'item_date_stock']); //checked
    Route::post('ext_qty',[AddItemController::class,'update_extra_qty']); //checked
    Route::post('stock_update',[AddItemController::class,'update_stock']); //checked
    Route::post('item_update',[AddItemController::class,'update_item']); //checked
    Route::get('fieldList',[AddItemController::class,'view_item']); //checked





// Invoice Controller Route  //14
    Route::get('invoice', [InvoiceController::class, 'add_received_showinvoice']);
    Route::get('receive-update-invoice', [InvoiceController::class, 'add_received_update']);
    
    Route::post('createInvoice', [InvoiceController::class, 'createInvoice']);
    Route::post('invoice-allData', [InvoiceController::class, 'delete_invoice_all_data_ByUserId']);
    Route::post('delete-invoice', [InvoiceController::class, 'delete_invoice']);
    Route::post('invoices-customer-name', [InvoiceController::class, 'find_customer_name']);
    Route::post('find-week-dates', [InvoiceController::class, 'find_week_dates']);
    Route::post('week_dates', [InvoiceController::class, 'this_week_date']); //checked

    Route::get('collect_amount',[InvoiceController::class,'to_collect_amount']); //checked
    Route::get('amoutDetails',[InvoiceController::class,'to_collect_amunt_details']); //checked
    Route::post('userBalance',[InvoiceController::class,'transaction_balance_amt']); //checked
    Route::post('invoice', [InvoiceController::class, 'update_invoice']); //checked
    Route::get('bill_invoice',[InvoiceController::class,'view_bill']); //checked
    Route::get('invoice_details',[InvoiceController::class,'view_invoice_details']); //checked






// Invoice_Item_Data Controller Route //5
    Route::post('invoice-item-data-allData', [InvoiceItemDataController::class, 'delete_invoice_item_data_all_data']);
    Route::post('invoice-item-data', [InvoiceItemDataController::class, 'delete_invoice_item_data_ByFinalId']);
    Route::post('invoice-item', [InvoiceItemDataController::class, 'invc_item_table']);//checked
    Route::post('invoice-copy', [InvoiceItemDataController::class, 'item_copy_data']); //checked

    Route::post('id_update',[InvoiceItemDataController::class,'update_final_id']); //checked



// Item_Details Controller Route //4
    Route::post('add-item-details', [ItemDetailsController::class, 'addItemDetail']);
    Route::post('delete-item-details-all-data', [ItemDetailsController::class, 'delete_item_details_all_data_ByUserId']);
    Route::post('delete-item-details', [ItemDetailsController::class, 'delete_item_details_ByItemId']);

    Route::get('item_details',[ItemDetailsController::class,'view_item_timeline']); //checked


// Party Controller Route  //7
    Route::post('party', [PartyController::class, 'createParty']);
    Route::post('show-partyName', [PartyController::class, 'show_partyName_unique']);
    Route::post('delete-party-all-data', [PartyController::class, 'delete_party_all_data']);
    Route::put('parties/{id}', [PartyController::class, 'edit_party']);
    Route::post('party_details', [PartyController::class, 'show_party_details']); //checked

    Route::delete('delete_party',[PartyController::class,'delete_party']);
    Route::get('party_name',[PartyController::class,'view_party']);   //checked



// Tax Controller //1
    Route::get('TotalTax',[TaxController::class,'total_tax_total_qty']); //checked

// Temp Invoice Controller  //11
    Route::post('add_data_temp',[TempInvoiceController::class,'temp_add_item_invoice']); //checked
    Route::delete('delete_data_temp',[TempInvoiceController::class,'temp_delete_invoice']);   //checked
    Route::get('get_total_items',[TempInvoiceController::class,'temp_item_count']); //checked
    Route::get('get_total_qty',[TempInvoiceController::class,'temp_total_qty']); //checked
    Route::get('get_sales_price',[TempInvoiceController::class,'temp_total']); //checked
    
    Route::post('update_tempDetails',[TempInvoiceController::class,'temp_update_item_details']);//checked
    Route::post('unvisible_stock',[TempInvoiceController::class,'unvisible_stock_item']); //checked
    Route::get('get_temp_invoice',[TempInvoiceController::class,'show_temp_invoice']); //checked
    Route::get('get_temp_qty',[TempInvoiceController::class,'visible']);           //checked   
    Route::get('get_temp_item',[TempInvoiceController::class,'view_temp_item']);  //checked



});
