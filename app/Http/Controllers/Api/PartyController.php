<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class PartyController extends Controller
{
    
public function createParty(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'party_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'gst_number' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'billing_address' => 'required|string|max:255',
            'shiping_address' => 'nullable|string|max:255',
            'shiping_state' => 'nullable|string|max:255',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 400);
        }

        $userId = $request->user()->id;
        $partyName = $request->input('party_name');

        // Check if the party name already exists for this user
        $existingParty = DB::table('party')->where('party_name', $partyName)->where('user_id', $userId)->first();
        if ($existingParty) {
            return response()->json([
                'success' => false,
                'error' => 'You already have a party with the same name.',
            ], 400);
        }

        // Insert the new party into the database
        $party = DB::table('party')->insertGetId([
            'user_id' => $userId,
            'party_name' => $partyName,
            'phone_number' => $request->input('phone_number'),
            'gst_number' => $request->input('gst_number'),
            'state' => $request->input('state'),
            'billing_address' => $request->input('billing_address'),
            'shiping_address' => $request->input('shiping_address'),
            'shiping_state' => $request->input('shiping_state'),
        ]);

        $partyData = DB::table('party')->where('id', $party)->first();

        return response()->json([
            'success' => true,
            'message' => 'Party created successfully',
            'user_data' => $partyData
        ], 201);
    }

    public function show_partyName_unique(Request $request)
{
    try {
        // Get the user ID of the logged-in user
        $userId = Auth::user()->id;

        // Get the party name from the request body
        $partyName = $request->get('party_name');

        // Create a SQL query to check if the party name exists for the logged-in user
        $sql = "SELECT COUNT(*) AS total FROM party WHERE party_name = '{$partyName}' AND user_id = {$userId}";

        // Execute the query and get the result
        $count = DB::selectOne($sql)->total;

        // If the party name exists, return a JSON response with the data
        if ($count > 0) {
            return response()->json([
                'success' => true,
                'total' => 1,
                'user_data' => [
                    'party_name' => $partyName,
                ],
            ]);
        } else {
            // Return a JSON response with a status of `false`
            return response()->json([
                'success' => false,
                'error' => 'Party name does not exist.',
            ]);
        }
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}


    public function delete_party_all_data(Request $request)
    {

        $userId = $request->user()->id;

        $userExists = DB::table('party')->where('user_id', $userId)->exists();

        if (!$userExists) {
            return response()->json(['success' => false,'error' => 'User not found.'], 404);
        }

        try {
            DB::table('party')->where('user_id', $userId)->delete();
            return response()->json(['success' => true,'message' => ' data deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'error' => 'An error occurred'], 500);
        }
    }


    public function edit_party(Request $request)
{
    $id = $request->input('id');
    $data = $request->all();
    unset($data['id']);

    DB::table('party')
        ->where('id', $id)
        ->update($data);

    $party = DB::table('party')->where('id', $id)->first();

    return response()->json([
        'success' => True,
        'message' => 'Party Updated',
        'user_data' => $party
    ], 200);
}

    public function show_party_details(Request $request)
    {
        $condition = null;
        $fieldList = 'party_name';

        $party_name = $request->input('party_name');
        $user_id = $request->user()->id;

        if ($party_name && $user_id) {
            $condition = " WHERE party_name = '$party_name' AND user_id = '$user_id'";
            $fieldList = '*';
        } elseif ($party_name) {
            return response()->json(['success' => false, 'error' => 'Both party_name and user_id are required']);
        }

        $sql = "SELECT $fieldList FROM party $condition";
        $result = DB::select($sql);

        if ($result) {
            $user_data = $result[0];
            return response()->json([
                'success' => true,
                'user_data' => $user_data,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'No data found',
            ]);
        }
    }


    public function delete_party(Request $request)
    {
        $response = [];

        $partyName = $request->input('party_name');
        $userId = $request->user()->id;

        if (empty($partyName)) {
            return response()->json([
                'success' => false,
                'error' => 'Required input(s) missing.',
            ], 400);
        }

        // Check if the party exists in the invoice table
        $invoiceCount = DB::table('invoice')
            ->where('customer_name', $partyName)
            ->count();

        if ($invoiceCount > 0) {
            return response()->json([
                'success' => false,
                'error' => 'Party cannot be deleted because it exists in the invoice table.',
            ], 401);
        }

        // Delete the party from the party table
        $deletedRows = DB::table('party')
            ->where('party_name', $partyName)
            ->delete();

        if ($deletedRows > 0) {
            return response()->json([
                'success' => true,
                'message' => 'Party deleted successfully.',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Party not found or not deleted.',
            ], 404);
        }
    }


    public function view_party(Request $request)
    {
        try {
            // Get the user's role.
            $user = $request->user()->id;

            // Query all party_names, filtered by the user's role.
            $parties = DB::table('party')
                ->leftJoin('invoice', 'party.party_name', '=', 'invoice.customer_name')
                ->select('party.id','party_name', DB::raw('SUM(balance_amount) AS balance_amount'))
                ->where('party.user_id', $user)
                ->groupBy('party_name','party.id')
                ->orderBy('party_name')
                ->get();

            $count = $parties->count();

            if ($count > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully retrieved user parties data.',
                    'total' => $count,
                    'data' => $parties,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'No parties found for the user with role ' . $role,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }




    public function view_balance(Request $request)
    {
        try {
            $user_id = $request->user()->id;

            $parties = DB::table('party')
                ->join('invoice', 'party.party_name', '=', 'invoice.customer_name')
                ->select('party_name', DB::raw('SUM(balance_amount) AS balance_amount'))
                ->where('party.user_id', $user_id)
                ->groupBy('party_name')
                ->having('balance_amount', '>', 0)
                ->get();

            $count = $parties->count();

            if ($count > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully retrieved user parties data.',
                    'total' => $count,
                    'data' => $parties,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'No parties found for the user with id ' . $user_id,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
































