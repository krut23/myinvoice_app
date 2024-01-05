<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use stdClass;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{

    public function createInvoice(Request $request)
    {
        $rules = [
            'final_id' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'gst_number' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'billing_address' => 'required|string|max:255',
            'sub_total' => 'required|string',
            'additional_charge_name' => 'required|string|max:255',
            'additional_charge' => 'required|string',
            'discount_percentage' => 'required|string',
            'discount' => 'required|string',
            'round_off' => 'required|string',
            'total_amount' => 'required|string',
            'cash_receive' => 'required|string',
            'balance_amount' => 'required|string',
            'total_item' => 'required|string',
            'due_date' => 'required|string',
            'invoice_date' => 'required|string',
            'item_id' => 'required|integer',
            'summary_date' => 'required|string',
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
            'final_id' => $request->input('final_id'),
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

    public function add_received_update(Request $request)
    {
        $id = $request->get('id');
        $customer_name = $request->get('customer_name');
        $phone_number = $request->get('phone_number');
        $cash_receive = $request->get('cash_receive');
        $balance_amount = $request->get('balance_amount');

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'cash_receive' => 'required|string',
            'balance_amount' => 'required|string',
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

    // Add the following code to show invoices for a particular customer name
    if ($request->has('customer_name')) {
        $customerName = $request->input('customer_name');
        $invoices = DB::select('select ' . $fieldList . ' from invoice where customer_name = "' . $customerName . '"');
    } else {
        $invoices = DB::select('select ' . $fieldList . ' from invoice ' . $condition);
    }

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
        $user_id = $request->user()->id;
        $id = $request->input('id');
        $summary_date = $request->input('summary_date');

        $condition = null;
        $fieldList = ["summary_date", "total_amount"];

        if (!empty($user_id) && !empty($id)) {
            $condition = " where id='$id' AND user_id='$user_id' AND summary_date ='$summary_date'";
        } elseif (!empty($user_id)) {
            $condition = " where user_id='$user_id' AND summary_date ='$summary_date'";
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

        $response = ["success" => true, "user_data" => []];

        $summary_date_total = [];

        foreach ($result as $row) {
            $summary_date = $row->summary_date;

            if (!isset($summary_date_total[$summary_date])) {
                $summary_date_total[$summary_date] = 0;
            }

            $summary_date_total[$summary_date] += $row->total_amount;
        }

        foreach ($summary_date_total as $summary_date => $total_amount) {
            $response["user_data"] = [
                "summary_date" => $summary_date,
                "total_amount" => $total_amount,
            ];
        }

        return response()->json($response);
    }


public function show_week_invoice(Request $request)
{
    $user_id = $request->user()->id;
    $id = $request->input('id');
    $summary_date = $request->input('summary_date');

    $condition = null;
    $fieldList = ["id", "invoice_date", "customer_name", "final_id", "total_amount", "summary_date"];

    if (!empty($user_id) && !empty($id)) {
        $condition = " where id='$id' AND user_id='$user_id' AND summary_date ='$summary_date'";
    } elseif (!empty($user_id)) {
        $condition = " where user_id='$user_id' AND summary_date ='$summary_date'";
    } elseif (!empty($id)) {
        $condition = " where id='$id'";
    } else {
        return response()->json(["success" => false, "error" => "Please provide either user_id or id"]);
    }

    $result = DB::select("select " . implode(',', $fieldList) . " from invoice " . $condition);

    $count = count($result);

    $response = ["success" => true,"total" => $count,"data" => [] ];

    foreach ($result as $row) {
        $response["data"][] = [
            "id" => $row->id,
            "invoice_date" => $row->invoice_date,
            "customer_name" => $row->customer_name,
            "final_id" => $row->final_id,
            "total_amount" => $row->total_amount,
            "summary_date" => $row->summary_date,
        ];
    }

    return response()->json($response);
}

    public function to_collect_amount(Request $request)
    {
        $user_id = $request->user()->id;

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

            // Remove the array type from the user_data response
            $user_data = array_pop($result);

            $response = [
                'success' => true,
                'total' => $count,
                'user_data' => $user_data,
            ];

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

        $response = [
            'success' => true,
        ];

        try {
            $sql = "select $FieldList from invoice $condition";
            $result = DB::select($sql);
            $count = count($result);

            $response['total'] = $count;
            $response['data'] = $result;

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function transaction_balance_amt(Request $request)
    {
        $user_id = $request->user()->id;
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
        $response = [];

        // Validate the input
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required',
            'phone_number' => 'required',
            'gst_number' => 'required',
            'state' => 'required',
            'billing_address' => 'required',
            'additional_charge_name' => 'required',
            'additional_charge' => 'required',
            'discount_percentage' => 'required',
            'discount' => 'required',
            'round_off' => 'required',
            'total_amount' => 'required',
            'cash_receive' => 'required',
            'balance_amount' => 'required',
            'due_date' => 'required',
        ]);

        if ($validator->fails()) {
            // Validation failed, return error response
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 400);
        }

        // Update the invoice
        $id = $request->input('id');

        $result = DB::table('invoice')
            ->where('id', $id)
            ->update($request->except(['id']));

        if (!$result) {
            // Invoice not found or update failed, return error response
            return response()->json(['success' => false, 'message' => 'Invoice not found or update failed'], 404);
        }

        $response['success'] = true;
        $response['message'] = 'Invoice updated successfully';

        return response()->json($response);
    }




public function view_bill(Request $request)
    {
        $response = [];

        $user_id = $request->user()->id;
        $condition = null;
        $fieldList = "id,final_id, item_id, customer_name, total_amount, due_date, invoice_date, balance_amount, cash_receive,summary_date";

        if ($user_id) {
            $condition = " where user_id = ?";
        }

        // Fetch invoice data
        $invoices = DB::select("select $fieldList from invoice $condition", [$user_id]);

        if (count($invoices) > 0) {
            // Get the user model from the request
            $user = $request->user();

            // Get the business name and business logo from the user model
            $businessName = $user->business_name;
            $businessLogo = $user->business_logo;


            return response()->json([
                'success' => true,
                'total' => count($invoices),
                'business_name' => $businessName,
                'business_logo' => $businessLogo,
                'data' => $invoices,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No invoices found for the specified user.',
            ], 404);
        }
    }
    
    
   public function view_bill_balance_amount(Request $request)
{
     $response = [];

    $user_id = $request->user()->id;
    $condition = null;
    $fieldList = "id,final_id, item_id, customer_name, total_amount, due_date, invoice_date, balance_amount, cash_receive,summary_date";

    if ($user_id) {
        $condition = " where balance_amount >0 and user_id = {$user_id}";
    }

    // Fetch invoice data
    $invoices = DB::select("select $fieldList from invoice $condition");

    if (count($invoices) > 0) {
        return response()->json([
            'success' => true,
            'total' => count($invoices),
            'data' => $invoices,
        ]);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'No invoices found for the specified user.',
        ], 404);
    }
}


public function view_invoice_details(Request $request)
{
    $response = [];

    $id = $request->input('id');
    $customerName = $request->input('customer_name');

    $fieldList = "id, customer_name, invoice_date, due_date, billing_address, gst_number, state, total_item, sub_total, additional_charge_name, additional_charge, discount, round_off, phone_number, total_amount, balance_amount, cash_receive";

    $query = DB::table('invoice');

    if (!empty($id) && !empty($customerName)) {
        $query->where('id', $id)->where('customer_name', 'like', "%$customerName%");
    } elseif (!empty($id)) {
        $query->where('id', $id);
        $fieldList = "*";
    } elseif (!empty($customerName)) {
        $query->where('customer_name', 'like', "%$customerName%");
    }

    $invoices = collect($query->selectRaw($fieldList)->get());

    if ($invoices->count() > 0) {
        $invoicesObject = new stdClass();
        foreach ($invoices as $invoice) {
            foreach ($invoice as $key => $value) {
                $invoicesObject->{$key} = $value;
            }
        }

        return response()->json([
            'success' => true,
            'total' => $invoices->count(),
            'user_data' => $invoicesObject,
        ]);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'No invoices found for the specified criteria.',
        ], 404);
    }
}


}
