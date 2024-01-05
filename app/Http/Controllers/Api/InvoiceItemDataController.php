<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InvoiceItemDataController extends Controller
{
    public function delete_invoice_item_data_all_data(Request $request)
    {

        $userId = $request->user()->id;

        $userExists = DB::table('invoice_item_data')->where('user_id', $userId)->exists();

        if (!$userExists) {
            return response()->json(['success' => false,'error' => 'User not found.'], 404);
        }

        try {
            DB::table('invoice_item_data')->where('user_id', $userId)->delete();
            return response()->json(['success' => true,'message' => 'Invoice data deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'error' => 'An error occurred'], 500);
        }
    }

    public function delete_invoice_item_data_ByFinalId(Request $request)
    {
        $finalId = $request->input('final_id');

        $finalIdExists = DB::table('invoice_item_data')->where('final_id', $finalId)->exists();

        if (!$finalIdExists) {
            return response()->json(['success' => false,'error' => 'FinalId not found.'], 404);
        }

        try {
            DB::table('invoice_item_data')->where('final_id', $finalId)->delete();
            return response()->json(['success' => true,'message' => 'Data deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'error' => 'An error occurred'], 500);
        }
    }

    public function invc_item_table(Request $request)
    {
        try {
            $query = "SELECT final_id, item_name, sales_price, total_sales_price, tax, total_tax, item_qty, item_id FROM invoice_item_data";

            if ($request->has('final_id')) {
                $finalId = $request->final_id;
                $query .= " WHERE final_id = '{$finalId}'";
            }

            if ($request->has('item_name')) {
                if (strpos($query, 'WHERE') !== false) {
                    $item_name = $request->item_name;
                    $query .= " AND item_name LIKE '%{$item_name}%'";
                } else {
                    $item_name = $request->item_name;
                    $query .= " WHERE item_name LIKE '%{$item_name}%'";
                }
            }

            $invoiceItemData = DB::select($query);

            return response()->json([
                'success' => true,
                'total' => count($invoiceItemData),
                'data' => $invoiceItemData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }



    public function item_copy_data(Request $request)
    {
        try {
            $userId = Auth::id();

            $userExists = DB::table('temp_invoice')->where('user_id', $userId)->exists();

            if (!$userExists) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            $sql = "INSERT INTO invoice_item_data (item_id, user_id, item_name, sales_price, total_sales_price, tax, total_tax, item_qty, final_id)
                SELECT item_id, user_id, item_name, sales_price, total_sales_price, tax, total_tax, item_qty, CAST(item_uuid AS CHAR)
                FROM temp_invoice
                WHERE user_id = '$userId'";

            DB::insert($sql);

            DB::table('temp_invoice')->where('user_id', $userId)->delete();

            $count = DB::table('invoice_item_data')->where('user_id', $userId)->count();

            return response()->json([
                'success' => true,
                'total' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function update_final_id(Request $request)
    {
        $response = [];

        $finalId = $request->input('final_id');
        $id = $request->input('id');
        $userId = $request->user()->id;

        if (!$id || !$userId) {
            return response()->json(['success' => false, 'error' => 'Input(s) missing'], 400);
        }

        $count = DB::table('invoice_item_data')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->count();

        if ($count === 0) {
            return response()->json([
                'success' => false,
                'error' => 'Record not found for the given id and user_id.',
            ], 404);
        }

        if ($finalId !== null) {
            DB::table('invoice_item_data')
                ->where('id', $id)
                ->where('user_id', $userId)
                ->update(['final_id' => $finalId]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated final_id'

        ]);
    }


}
