<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{

    public function createInvoice(Request $request)
    {
        $rules = [
            'customer_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'gst_number' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'billing_address' => 'required|string|max:255',
            'sub_total' => 'required|numeric',
            'additional_charge_name' => 'required|string|max:255',
            'additional_charge' => 'required|numeric',
            'discount_percentage' => 'required|numeric',
            'discount' => 'required|numeric',
            'round_off' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'cash_receive' => 'required|numeric',
            'balance_amount' => 'required|numeric',
            'total_item' => 'required|integer',
            'due_date' => 'required|date',
            'invoice_date' => 'required|date',
            'item_id' => 'required|integer',
            'summary_date' => 'required|date',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()
            ]);
        }

        $invoice = [
            'user_id' => Auth::id(),
            'customer_name' => $request->input('customer_name'),
            'phone_number' => $request->input('phone_number'),
            'gst_number' => $request->input('gst_number'),
            'state' => $request->input('state'),
            'billing_address' => $request->input('billing_address'),
            'sub_total' => $request->input('sub_total'),
            'additional_charge_name' => $request->input('additional_charge_name'),
            'additional_charge' => $request->input('additional_charge'),
            'discount_percentage' => $request->input('discount_percentage'),
            'discount' => $request->input('discount'),
            'round_off' => $request->input('round_off'),
            'total_amount' => $request->input('total_amount'),
            'cash_receive' => $request->input('cash_receive'),
            'balance_amount' => $request->input('balance_amount'),
            'total_item' => $request->input('total_item'),
            'due_date' => $request->input('due_date'),
            'invoice_date' => $request->input('invoice_date'),
            'item_id' => $request->input('item_id'),
            'summary_date' => $request->input('summary_date')
        ];

        DB::table('invoice')->insert($invoice);

        return response()->json([
            'success' => true,
            'message' => 'Item Added',
            'Data' => $invoice
        ]);
    }


    public function add_received_showinvoice(Request $request)
    {
        $id = $request->get('id');

        $sql = "select id, customer_name, balance_amount, phone_number, cash_receive from invoice";
        if ($id !== null) {
            $sql .= " where id = $id";
        }

        $results = DB::select($sql);

        if (count($results) === 0) {
            return response()->json([
                'success' => false,
                'error' => 'Invoice not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    public function add_received_update(Request $request, $id)
    {
        $customer_name = $request->get('customer_name');
        $phone_number = $request->get('phone_number');
        $cash_receive = $request->get('cash_receive');
        $balance_amount = $request->get('balance_amount');

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'cash_receive' => 'required|numeric',
            'balance_amount' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors(),
            ], 400);
        }

        $sql = "update invoice set customer_name='$customer_name',phone_number='$phone_number',cash_receive='$cash_receive',balance_amount='$balance_amount' where id='$id' ";
        DB::update($sql);

        $invoice = DB::table('invoice')->where('id', $id)->first();

        return response()->json([
            'success' => 'true',
            'message' => 'Invoice updated successfully',
            'invoice' => $invoice,
        ]);
    }
    


    public function delete_invoice_all_data_ByUserId(Request $request)
    {
        $userId = $request->user()->id;

        $userExists = DB::table('invoice')->where('user_id', $userId)->exists();

        if (!$userExists) {
            return response()->json(['success' => false, 'error' => 'User not found.'], 404);
        }

        try {
            DB::table('invoice')->where('user_id', $userId)->delete();
            return response()->json(['success' => true, 'message' => 'Data deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'error' => 'An error occurred'], 500);
        }
    }

    public function delete_invoice(Request $request)
    {
        $Id = $request->input('id');

        $userExists = DB::table('invoice')->where('id', $Id)->exists();

        if (!$userExists) {
            return response()->json(['success' => false,'error' => 'User not found.'], 404);
        }

        try {
            DB::table('invoice')->where('id', $Id)->delete();
            return response()->json(['success' => true,'message' => 'Data deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'error' => 'An error occurred'], 500);
        }
    }

    public function find_customer_name(Request $request)
    {
        $condition = '';
        $fieldList = 'user_id,customer_name';

        if ($request->has('user_id') || $request->has('customer_name')) {
            $condition = 'where user_id like "%' . $request->input('user_id') . '%' .
                ' && customer_name like "%' . $request->input('customer_name') . '%"';
            $fieldList = '*';
        }

        $invoices = DB::select('select ' . $fieldList . ' from invoice ' . $condition);

        $response = [
            'success' => true,
            'total' => count($invoices),
            'data' => $invoices,
        ];

        return response()->json($response);
    }


    public function find_week_dates(Request $request)
    {
        $summary_date = $request->input('summary_date');
        $user_id = $request->user()->id;
        $customer_name = $request->input('customer_name');

        $condition = null;
        $fieldList = ['summary_date'];

        if ($summary_date || $user_id || $customer_name) {
            $condition = DB::table('invoice')
                ->where('summary_date', $summary_date)
                ->where('user_id', $user_id)
                ->where('customer_name', $customer_name);

            $fieldList = ['*'];
        }

        $count = $condition->count();
        $response = [ 'success' => true,'total' => $count];
        $response['data'] = $condition->get();

        return response()->json($response);
    }

    public function this_week_date(Request $request)
    {
        $user_id = $request->input('user_id');
        $id = $request->input('id');
    
        $condition = null;
        $fieldList = ["summary_date"];
    
        if (!empty($user_id) && !empty($id)) {
            $condition = " where id='$id' AND user_id='$user_id'";
        } elseif (!empty($user_id)) {
            $condition = " where user_id='$user_id'";
        } elseif (!empty($id)) {
            $condition = " where id='$id'";
        } else {
            return response()->json(["success" => false, "error" => "Please provide either user_id or id"]);
        }
    
        $result = DB::select("select " . implode(',', $fieldList) . " from invoice " . $condition);
    
        $count = count($result);
    
        if ($count === 0) {
            return response()->json(["success" => false, "error" => "No records found"]);
        }
    
        $response = ["success" => true, "total" => $count, "summary_dates" => []];
    
        foreach ($result as $row) {
            $response["summary_dates"][] = $row->summary_date;
        }
    
        return response()->json($response);
    }
    
        

    public function to_collect_amount(Request $request)
    {
        $user_id = $request->input('user_id');
    
        if (!$user_id) {
            return response()->json([
                'success' => false,
                'error' => 'Missing user_id',
            ], 400);
        }
    
        $condition = " WHERE user_id = '$user_id'";
        $response = [];
    
        try {
            $sql = "SELECT SUM(balance_amount) AS totalsum FROM invoice $condition";
            $result = DB::select($sql);
    
            $count = count($result);
    
            if ($count === 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'No records found',
                ], 404);
            }
    
            $response[] = ['total' => $count];
    
            foreach ($result as $row) {
                $response[] = $row;
            }
    
            $response[] = ['success' => true];
    
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    public function to_collect_amunt_details(Request $request)
    {
        $condition = null;
        $FieldList = "balance_amount,total_amount";
    
        if (isset($request->user_id)) {
            $condition = " where balance_amount > 0 && user_id='$request->user_id'";
            $FieldList = "*";
        }
    
        $response = [];
    
        try {
            $sql = "select $FieldList from invoice $condition";
            $result = DB::select($sql);
            $count = count($result);
            $response[] = ["total" => $count];
    
            if ($count > 0) {
                foreach ($result as $row) {
                    $response[] = $row;
                }
                $response[] = ['success' => true, ];
                return response()->json($response);
            } else {
                return response()->json(['success' => false, 'error' => 'No records found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    

    public function transaction_balance_amt(Request $request)
    {
        $user_id = $request->input('user_id');
        $customer_name = $request->input('customer_name');

        $response = [];

        if ($user_id) {
            $condition = " WHERE user_id = $user_id";
            if ($customer_name) {
                $condition .= " AND customer_name = '$customer_name'";
            }
        } elseif ($customer_name) {
            $condition = " WHERE customer_name = '$customer_name'";
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Missing user_id or customer_name',
            ], 400);
        }

        $sql = "SELECT SUM(balance_amount) AS totalsum FROM invoice $condition";
        $result = DB::select($sql);

        if (empty($result)) {
            return response()->json([
                'success' => false,
                'error' => 'No records found',
            ], 404);
        }

        $totalSum = $result[0]->totalsum;

        $response[] = [
            'success' => true,
            'total' => $totalSum,
        ];

        return response()->json($response);
    }

     


    public function update_invoice(Request $request)
    {
        $id = $request->input('id', $request->query('id'));
    
        if ($id === null) {
            return response()->json([
                'error' => 'ID parameter is missing'
            ], 400);
        }
    
        $updateField = $request->input('update_field');
    
        if ($updateField === null) {
            return response()->json([
                'error' => 'Update field is missing'
            ], 400);
        }
    
        $updateValue = $request->input($updateField);
    
        $allowedFields = [
            'customer_name',
            'phone_number',
            'gst_number',
            'state',
            'billing_address',
            'additional_charge_name',
            'additional_charge',
            'discount_percentage',
            'discount',
            'total_amount',
            'cash_receive',
            'balance_amount',
            'due_date'
        ];
    
        if ($updateField !== 'all' && !in_array($updateField, $allowedFields)) {
            return response()->json([
                'error' => 'Invalid update field'
            ], 400);
        }
    
        $dataToUpdate = [];
    
        if ($updateField === 'all') {
    
            foreach ($allowedFields as $field) {
                $dataToUpdate[$field] = $request->input($field);
            }
        } else {
    
            $dataToUpdate[$updateField] = $updateValue;
        }
    
        $updateResult = DB::table('invoice')
            ->where('id', $id)
            ->update($dataToUpdate);
    
        if ($updateResult) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice updated'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update invoice'
            ], 400);
        }
    }
    
    


    public function view_bill(Request $request)
    {

        $id = $request->input('id');
        $customer_name = $request->input('customer_name');


        $fieldList = "id, item_id, customer_name, total_amount, due_date, invoice_date, balance_amount, total_amount, cash_receive";
        $condition = "";


        if ($id !== null) {

            if ($customer_name !== null) {
                $condition = " WHERE  id = ? AND customer_name LIKE ?";
                $params = [ $id, "%$customer_name%"];
            } else {
                $condition = "  id = ?";
                $params = [$id, $customer_name];
            }
        }


        $result = DB::select("SELECT $fieldList FROM invoice $condition", $params);
        $response = [];


        if (count($result) > 0) {
          
            $response['success'] = true;
            $response['data'] = $result;
            } else {
            $response['error'] = 'Data not found';
            $response['success'] = false;
            }
                
            return response()->json($response, 200);
    }

    public function view_invoice_details(Request $request)
    {
        
        $id = $request->input('id');
        $customer_name = $request->input('customer_name');

        
        $fieldList = "id, customer_name, invoice_date, due_date, billing_address, gst_number, state, total_item, sub_total, additional_charge_name, additional_charge, discount, round_off, phone_number, total_amount, balance_amount, cash_receive";
        $condition = "";

       
        if ($id !== null) {
           
            if ($customer_name !== null) {
                $condition = " WHERE id = ? AND customer_name LIKE ?";
                $params = [$id, "%$customer_name%"];
            } else {
                $condition = " WHERE id = ?";
                $params = [$id];
            }
        }
        
        else if ($customer_name !== null) {
            $condition = " WHERE customer_name LIKE ?";
            $params = ["%$customer_name%"];
        }

        
        $result = DB::select("SELECT $fieldList FROM invoice $condition", $params);
        
         $response = [];

        
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
