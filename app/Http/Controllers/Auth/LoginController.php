<?php

namespace App\Http\Controllers\Auth;

use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('user_name', 'password');
        $token = Auth::attempt($credentials);


        if ($token) {
            $user = Auth::user();
         // Update the remember_token column in the database
         $user->remember_token = $token;
         $user->save();
            return response()->json([
                'status' => true,
                'authorization' => $token,
                'user' => $user,
            ]);
        } else {

            return response()->json([
                'status' => false,
                'error' => 'Invalid User Name and Password',
            ], 401);
        }
    }

    public function updatePassword(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();

        // Validate the input
        $validator = \Validator::make($request->all(), [
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all(),
            ], 400);
        }

           // Update the user's password
           DB::table('users')->update([
            'password' => Hash::make($input['password']),
        ]);

        // Return a success response
        return response()->json([
            'success' =>  true,
            'message' => 'Password updated successfully.',
        ], 200);
    }

   public function delete()
    {
    // Get the logged-in user's ID.
    $userId = Auth::guard('api')->user()->id;

    // Filter the data in the tables to only include the logged-in user's data.
    $categoryData = DB::table('category')->where('user_id', $userId)->get();
    $invoiceData = DB::table('invoice')->where('user_id', $userId)->get();
    $invoiceItemData = DB::table('invoice_item_data')->where('user_id', $userId)->get();
    $itemData = DB::table('item')->where('user_id', $userId)->get();
    $itemDetailsData = DB::table('item_details')->where('user_id', $userId)->get();
    $partyData = DB::table('party')->where('user_id', $userId)->get();
    $tempInvoiceData = DB::table('temp_invoice')->where('user_id', $userId)->get();

    // Delete the filtered data from the tables.
    DB::table('category')->where('user_id', $userId)->delete();
    DB::table('invoice')->where('user_id', $userId)->delete();
    DB::table('invoice_item_data')->where('user_id', $userId)->delete();
    DB::table('item')->where('user_id', $userId)->delete();
    DB::table('item_details')->where('user_id', $userId)->delete();
    DB::table('party')->where('user_id', $userId)->delete();
    DB::table('temp_invoice')->where('user_id', $userId)->delete();


    // Return a success response.
    return response()->json(['success' => true,'message' => 'deleted all data']);
    }
    
    public function logout()
    {
        $title = 'Logout';
        Session::forget('switchBackProfileUserId');
        Auth::logout();
// Return a success response.
    return response()->json(['success' => true,'message' => 'Log out']);    }
}
