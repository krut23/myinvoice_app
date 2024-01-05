<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TempInvoiceController extends Controller
{
    public function temp_add_item_invoice(Request $request)
    {
        $response = [];

        $user_id = $request->user()->id;
        $item_id = $request->input('item_id');
        $item_name = $request->input('item_name');
        $sales_price = $request->input('sales_price');
        $total_sales_price = $request->input('total_sales_price');
        $tax = $request->input('tax');
        $total_tax = $request->input('total_tax');
        $item_qty = $request->input('item_qty');
        $total_item_qty = $request->input('total_item_qty');
        $item_uuid = $request->input('item_uuid');

        if (
            $user_id === null ||
            $item_id === null ||
            $item_name === null ||
            $sales_price === null ||
            $total_sales_price === null ||
            $tax === null ||
            $total_tax === null ||
            $item_qty === null ||
            $total_item_qty === null ||
            $item_uuid === null
        ) {
            return response()->json(['success' => false, 'error' => 'Input(s) missing'], 400);
        }

        try {
            DB::table('temp_invoice')->insert([
                'user_id' => $user_id,
                'item_id' => $item_id,
                'item_name' => $item_name,
                'sales_price' => $sales_price,
                'total_sales_price' => $total_sales_price,
                'tax' => $tax,
                'total_tax' => $total_tax,
                'item_qty' => $item_qty,
                'total_item_qty' => $total_item_qty,
                'item_uuid' => $item_uuid,
            ]);

            $response['success'] = true;
            $response['message'] = 'temp Item Added';

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to add item'], 500);
        }
    }




    public function temp_delete_invoice(Request $request)
    {
        $response = [];

        $item_id = $request->input('item_id');

        if ($item_id === null) {
            return response()->json(['error' => 'item_id is missing'], 400);
        }

        $result = DB::table('temp_invoice')->where('item_id', $item_id)->delete();

        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Item deleted';
            return response()->json($response, 200);
        } else {
            $response['success'] = false;
            $response['error'] = 'Item not found';
            return response()->json($response, 500);
        }
    }


    public function temp_item_count(Request $request)
    {
        $response = [];

        $user_id = $request->user()->id;

        if ($user_id === null) {
            return response()->json(['error' => 'user_id is missing'], 400);
        }

        $totalItemQty = DB::table('temp_invoice')
            ->where('user_id', $user_id)
            ->sum('item_qty');

        if ($totalItemQty > 0) {
            $response['success'] = true;
            $response['total_item'] = $totalItemQty;
            return response()->json($response, 200);
        } else {
            $response['success'] = false;
            return response()->json($response, 500);
        }
    }



    public function temp_total_qty(Request $request)
    {
        $response = [];

        $user_id = $request->user()->id;

        if ($user_id === null) {
            return response()->json(['failure' => 'user_id is missing'], 400);
        }

        $totalItemQty = DB::table('temp_invoice')
            ->where('user_id', $user_id)
            ->sum('total_item_qty');

        if ($totalItemQty > 0) {
            $response['success'] = true;
            $response['total_qty'] = $totalItemQty;
            return response()->json($response, 200);
        } else {
            $response['success'] = false;
            return response()->json($response, 500);
        }
    }



    public function temp_total(Request $request)
    {
        $response = [];

        $user_id = $request->user()->id;

        if ($user_id === null) {
            return response()->json(['error' => 'user_id is missing'], 400);
        }

        $totalSalesPrice = DB::table('temp_invoice')
            ->where('user_id', $user_id)
            ->sum('total_sales_price');

        if ($totalSalesPrice > 0) {
            $response['success'] = true;
            $response['total_Sales_Price'] = $totalSalesPrice;
            return response()->json($response, 200);
        } else {
            $response['success'] = false;
            return response()->json($response, 500);
        }
    }





    public function temp_update_item_details(Request $request)
    {
        $request->validate([
            'item_id' => 'required|numeric',
        ]);

        $updateFields = [];

        foreach ($request->input() as $key => $value) {
            if ($key !== 'item_id') {
                $updateFields[$key] = $value;
            }
        }
        try {
            DB::table('temp_invoice')
                ->where('item_id', $request->input('item_id'))
                ->update($updateFields);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Stock Updated',
        ], 200);
    }


    public function unvisible_stock_item(Request $request)
    {
        $response = [];

        $item_id = $request->input('item_id');
        $item_qty = $request->input('item_qty');

        if ($item_id === null || $item_qty === null) {
            return response()->json(['success' => false, 'error' => 'Input(s) is missing'], 400);
        }

        $result = DB::table('temp_invoice')
            ->where('item_id', $item_id)
            ->update(['item_qty' => $item_qty]);

            if ($result > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stock Updated',
                    'data' => $result,
                ]);
        } else {
            return response()->json(['success' => false, 'error' => 'Failed to update stock'], 500);
        }
    }


    public function show_temp_invoice(Request $request)
    {
        $response = [];

        $user_id = $request->user()->id;
        $selectFields = $request->input('fields', '*');

        $fieldList = [
            'id',
            'item_id',
            'item_name',
            'sales_price',
            'tax',
            'total_sales_price',
            'total_tax',
            'item_qty',
            'total_item_qty',
        ];

        $query = DB::table('temp_invoice')
            ->select(DB::raw($selectFields))
            ->where('user_id', $user_id);

        $results = $query->get();

        $totalSalesPrice = DB::table('temp_invoice')
        ->where('user_id', $user_id)
        ->sum('total_sales_price');
        
         $totalSalesPrice = number_format($totalSalesPrice,2, '.', '');

        $count = count($results);
        if ($count > 0) {
            return response()->json([
                'success' => true,
                'total' => $count,
                'total_temp_sales_price' => $totalSalesPrice,
                'data' => $results,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'No records found '
            ]);
        }
    }


    public function visible(Request $request)
    {
        $item_id = $request->input('item_id');

        if ($item_id === null) {
            return response()->json(['error' => 'item_id is missing'], 400);
        }

        $query = DB::table('temp_invoice')
            ->select('item_id', 'item_qty')
            ->where('item_id', 'like', "$item_id%");

        $results = $query->get();

        // Remove the array type from the response data
        $data = [];
        foreach ($results as $result) {
            $data = [
                'item_id' => $result->item_id,
                'item_qty' => $result->item_qty,
            ];
        }

        $count = count($results);

        if ($count > 0) {
            return response()->json([
                'success' => true,
                'total' => $count,
                'user_data' => $data,
            ]);
        } else {
            return response()->json(['success' => false, 'error' => 'No records found'], 404);
        }
    }



    public function view_temp_item(Request $request)
    {
        try {
            $item_id = $request->input('item_id');
            $item_name = $request->input('item_name');
            $selectFields = $request->input('fields', 'item_id,item_name,sales_price,tax,item_qty');

            $fieldList = explode(',', $selectFields);

            $query = DB::table('temp_invoice')->select($fieldList);

            if ($item_id !== null) {
                $query->where('item_id', $item_id);
            }

            if ($item_name !== null) {
                $query->where('item_name', 'like', "%$item_name%");
            }

            $results = $query->orderBy('item_id', 'desc')->get();

            if ($results) {
                $userData = [];
                foreach ($results as $result) {
                    $userData = $result;
                }

                return response()->json([
                    'success' => true,
                    'user_data' =>  $userData,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'No records found',
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
