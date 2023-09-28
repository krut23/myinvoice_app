<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ItemDetailsController extends Controller
{
     public function addItemDetail(Request $request){

        $validator = Validator::make($request->all(), [
            'item_id' => 'required|numeric',
            'name' => 'required|string|unique:item_details,name',
            'date' => 'required|string',
            'pcs_change' => 'required|string',
            'final_pcs' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $userId= $request->user()->id;
        $item_id = $request->input('item_id');
        $name = $request->input('name');
        $date = $request->input('date');
        $pcsChange = $request->input('pcs_change');
        $finalPcs = $request->input('final_pcs');

        DB::table('item_details')->insert([
            'user_id' => $userId,
            'item_id' => $item_id,
            'name' => $name,
            'date' => $date,
            'pcs_change' => $pcsChange,
            'final_pcs' => $finalPcs
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item added successfully'
        ], 201);
    }

    public function delete_item_details_all_data_ByUserId(Request $request)
    {

        $userId = $request->input('user_id');

        // Check if the user exists in the "item_details" table
        $userExists = DB::table('item_details')->where('user_id', $userId)->exists();

        if (!$userExists) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        try {
            DB::table('item_details')->where('user_id', $userId)->delete();
            return response()->json(['message' => ' data deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function delete_item_details_ByItemId(Request $request)
    {

        $itemId = $request->input('item_id');

        // Check if the user exists in the "item_details" table
        $itemExists = DB::table('item_details')->where('item_id', $itemId)->exists();

        if (!$itemExists) {
            return response()->json(['error' => 'Item not found.'], 404);
        }

        try {
            DB::table('item_details')->where('item_id', $itemId)->delete();
            return response()->json(['message' => ' data deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function view_item_timeline(Request $request)
    {
        $item_id = $request->input('item_id');

        $fieldList = "id, name, date, pcs_change, final_pcs, item_id";
        $condition = null;

        if ($item_id !== null) {
            $condition = "WHERE item_id = ?";
            $fieldList = "*";
        }

        $result = DB::select("SELECT $fieldList FROM item_details $condition", [$item_id]);
        $response = [];
        $response[] = ['success' => true];


        if (count($result) > 0) {
          
            $response['success'] = true;
            $response['data'] = $result;
            } else {
            $response['error'] = 'Data not found';
            $response['success'] = false;
            }
                
            return response()->json($response, 200);


    }
}
