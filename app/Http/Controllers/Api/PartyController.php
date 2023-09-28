<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class PartyController extends Controller
{
    public function createParty(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'party_name' => 'required|string|max:255|unique:party,party_name',
            'phone_number' => 'required|string|max:255',
            'gst_number' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'billing_address' => 'required|string|max:255',
            'shiping_address' => 'required|string|max:255',
            'shiping_state' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 400);
        }

        $userId = $request->user()->id;
        $partyName = $request->input('party_name');
        $phoneNumber = $request->input('phone_number');
        $gstNumber = $request->input('gst_number');
        $state = $request->input('state');
        $billingAddress = $request->input('billing_address');
        $shippingAddress = $request->input('shiping_address');
        $shippingState = $request->input('shiping_state');

        $party = DB::table('party')->insertGetId([
            'user_id' => $userId,
            'party_name' => $partyName,
            'phone_number' => $phoneNumber,
            'gst_number' => $gstNumber,
            'state' => $state,
            'billing_address' => $billingAddress,
            'shiping_address' => $shippingAddress,
            'shiping_state' => $shippingState,
        ]);
        $partyData = DB::table('party')->where('id', $party)->first();

        return response()->json([
            'success' => true,
            'message' => 'Party created successfully',
            'Data' => $partyData
        ], 201);
    }

    public function show_partyName_unique(Request $request)
    {
        $FieldList = 'user_id,party_name';

        if (isset($request['user_id']) || isset($request['party_name'])) {
            $condition = " where user_id = {$request['user_id']}";
        }

        try {
            $sql = "select $FieldList from party $condition";
            $parties = DB::select($sql);

            return response()->json([
                'success' => 'true',
                'total' => count($parties),
                'data' => $parties,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => 'false',
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

    public function edit_party(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'party_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:10',
            'gst_number' => 'required|string|max:15',
            'state' => 'required|string|max:255',
            'billing_address' => 'required|string',
            'shiping_address' => 'required|string',
            'shiping_state' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $party_name = $request->input('party_name');
        $phone_number = $request->input('phone_number');
        $gst_number = $request->input('gst_number');
        $state = $request->input('state');
        $billing_address = $request->input('billing_address');
        $shiping_address = $request->input('shiping_address');
        $shiping_state = $request->input('shiping_state');

        DB::table('party')
            ->where('id', $id)
            ->update([
                'party_name' => $party_name,
                'phone_number' => $phone_number,
                'gst_number' => $gst_number,
                'state' => $state,
                'billing_address' => $billing_address,
                'shiping_address' => $shiping_address,
                'shiping_state' => $shiping_state,
            ]);
            $party = DB::table('party')->where('id', $id)->first();

        return response()->json([
            'success' => True,
            'message' => 'Party Updated',
            'Data' => $party
        ], 200);
    }


    public function show_party_details(Request $request)
    {
        $condition = null;
        $fieldList = 'party_name';
    
        $party_name = $request->input('party_name');
        $user_id = $request->input('user_id');
    
        if ($party_name && $user_id) {
            $condition = " WHERE party_name = '$party_name' AND user_id = '$user_id'";
            $fieldList = '*';
        } elseif ($party_name || $user_id) {
            return response()->json(['success' => false, 'error' => 'Both party_name and user_id are required']);
        }
    
        $sql = "SELECT $fieldList FROM party $condition";
        $result = DB::select($sql);
    
        if (count($result) > 0) {
            return response()->json([
                'success' => true,
                'total' => count($result),
                'data' => $result,
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

        $party_name = $request->input('party_name');
        $user_id = $request->user()->id;


        if ($party_name === null || $user_id === null) {
            return response()->json([
                'success' => false,
                'error' => 'Input(s) missing'
            ], 400);
        }
        $partyExists = DB::table('party')->where('party_name', $party_name)->exists();

        if (!$partyExists) {
            return response()->json(['success' => false,'error' => 'Party not found.'], 404);
        }
        DB::table('party')
            ->where('party_name', $party_name)
            ->where('user_id', $user_id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Party deleted'
        ]);
    }

    public function view_party(Request $request)
    {
        try {
            $user_id = $request->input('user_id');
    
            if (!$user_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'missing_user_id',
                    'message' => 'user_id parameter is required.',
                ]);
            }
    
            $parties = DB::table('party')
                ->select('party_name')
                ->where('user_id', $user_id)
                ->get();
    
            $count = $parties->count();
    
            if ($count > 0) {
                return response()->json([
                    'success' => true,
                    'error' => 'no_error',
                    'message' => 'Successfully retrieved user parties data.',
                    'total' => $count,
                    'data' => $parties,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'no_data',
                    'message' => 'No parties found for the user with id ' . $user_id,
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
































