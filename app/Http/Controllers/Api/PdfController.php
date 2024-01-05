<?php

namespace App\Http\Controllers\Api;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class PdfController extends Controller
{
    public function numberToWord($num = '')
    {
        $num    = ( string ) ( ( int ) $num );

        if( ( int ) ( $num ) && ctype_digit( $num ) )
        {
            $words  = array( );

            $num    = str_replace( array( ',' , ' ' ) , '' , trim( $num ) );

            $list1  = array('','one','two','three','four','five','six','seven',
                'eight','nine','ten','eleven','twelve','thirteen','fourteen',
                'fifteen','sixteen','seventeen','eighteen','nineteen');

            $list2  = array('','ten','twenty','thirty','forty','fifty','sixty',
                'seventy','eighty','ninety','hundred');

            $list3  = array('','thousand','million','billion','trillion',
                'quadrillion','quintillion','sextillion','septillion',
                'octillion','nonillion','decillion','undecillion',
                'duodecillion','tredecillion','quattuordecillion',
                'quindecillion','sexdecillion','septendecillion',
                'octodecillion','novemdecillion','vigintillion');

            $num_length = strlen( $num );
            $levels = ( int ) ( ( $num_length + 2 ) / 3 );
            $max_length = $levels * 3;
            $num    = substr( '00'.$num , -$max_length );
            $num_levels = str_split( $num , 3 );

            foreach( $num_levels as $num_part )
            {
                $levels--;
                $hundreds   = ( int ) ( $num_part / 100 );
                $hundreds   = ( $hundreds ? ' ' . $list1[$hundreds] . ' Hundred' . ( $hundreds == 1 ? '' : 's' ) . ' ' : '' );
                $tens       = ( int ) ( $num_part % 100 );
                $singles    = '';

                if( $tens < 20 ) { $tens = ( $tens ? ' ' . $list1[$tens] . ' ' : '' ); } else { $tens = ( int ) ( $tens / 10 ); $tens = ' ' . $list2[$tens] . ' '; $singles = ( int ) ( $num_part % 10 ); $singles = ' ' . $list1[$singles] . ' '; } $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_part ) ) ? ' ' . $list3[$levels] . ' ' : '' ); } $commas = count( $words ); if( $commas > 1 )
            {
                $commas = $commas - 1;
            }

            $words  = implode( ', ' , $words );

            $words  = trim( str_replace( ' ,' , ',' , ucwords( $words ) )  , ', ' );
            if( $commas )
            {
                $words  = str_replace( ',' , ' and' , $words );
            }

            return $words;
        }
        else if( ! ( ( int ) $num ) )
        {
            return 'Zero';
        }
        return '';
    }



 public function invoiceGenerate(Request $request)
    {
            $final_id = $request->input('final_id');

        // Define the PDF file URL variable
        $pdf_file_url = asset('storage/invoices/' . $final_id . '.pdf');

        // Get the invoice data from the database
        $invoice = DB::table('invoice')->where('final_id', $final_id)->first();
        $invoice_item_data = DB::table('invoice_item_data')->where('final_id', $final_id)->get();

        // Get the user data from the database
        $users = DB::table('users')->where('id', $invoice->user_id)->first();
        $signature = $users->signature;

        // Get the business logo from the database
        $business_logo = asset('/storage/business_logos/' . $users->business_logo);
        $business_name = DB::table('users')->where('id', $invoice->user_id)->first();

        // Count the quantity
        $totalQuantity = 0;
        foreach ($invoice_item_data as $invoice_item) {
            $totalQuantity += $invoice_item->item_qty;
        }

        // Count the rate
        $totalRate = 0;
        foreach ($invoice_item_data as $invoice_item) {
            $totalRate += $invoice_item->sales_price * $invoice_item->item_qty;
        }

        // Count the tax
        $totalTax = 0;
        foreach ($invoice_item_data as $invoice_item) {
            $totalTax += $invoice_item->total_tax;
        }

        // Count the amount
        $totalAmount = 0;
        foreach ($invoice_item_data as $invoice_item) {
            $totalAmount += $invoice_item->total_sales_price;
        }

        // Generate the PDF file
        $pdf = PDF::loadView('invoice', [
            'invoice' => $invoice,
            'invoice_item_data' => $invoice_item_data,
            'business_logo' => $business_logo,
            'business_name' => $business_name,
            'users' => $users,
            'totalQuantity' => $totalQuantity,
            'totalRate' => $totalRate,
            'totalTax' => $totalTax,
            'totalAmount' => $totalAmount,
            'signature' => $signature,
            'word' => $this->numberToWord($totalAmount),
            'pdf_file_url' => $pdf_file_url
        ]);
        $pdf->render();

        // Save the PDF file to the database
    $pdf_file = $pdf->output();
    Storage::put('public/invoices/' . $final_id . '.pdf', $pdf_file, 'public');

    // Update the PDF file URL in the invoice table
    $invoice->pdf_file_url = $pdf_file_url;
    DB::table('invoice')->where('final_id', $final_id)->update(['pdf_file_url' => $pdf_file_url]);

    // Return the PDF file URL
    return response()->json([
        'success' => true,
        'pdf_file_url' => $pdf_file_url
    ]);
    }


      public function downloadPDF($final_id)
    {
        $invoice = DB::table('invoice')->where('final_id', $final_id)->first();
        $invoice_item_data = DB::table('invoice_item_data')->where('final_id', $final_id)->get();
        $users = DB::table('users')->where('id', $invoice->user_id)->first();
        $business_logo = asset('/storage/business_logos/' . $users->business_logo);
        $signature = $users->signature;

         // Count the quantity
         $totalQuantity = 0;
         foreach ($invoice_item_data as $invoice_item) {
             $totalQuantity += $invoice_item->item_qty;
         }

         // Count the rate
         $totalRate = 0;
         foreach ($invoice_item_data as $invoice_item) {
             $totalRate += $invoice_item->sales_price * $invoice_item->item_qty;
         }

         // Count the tax
         $totalTax = 0;
         foreach ($invoice_item_data as $invoice_item) {
             $totalTax += $invoice_item->total_tax;
         }

         // Count the amount
         $totalAmount = 0;
         foreach ($invoice_item_data as $invoice_item) {
             $totalAmount += $invoice_item->total_sales_price;
         }

         $word= $this->numberToWord($totalAmount);

       // Generate the PDF file.
       $pdf = PDF::loadView('invoice', compact('business_logo','users','invoice','invoice_item_data','totalQuantity','totalRate','totalTax','totalAmount','word','signature'));

    //    // Save the PDF file to storage.
    //    Storage::put('public/pdf/invoice.pdf', $pdf->output());

    //    // Download the PDF file from storage.
    //    return Storage::download('public/pdf/invoice.pdf');

     // Convert the PDF object to a string.
          $content = $pdf->output();

          // Create a new response object and set the content.
          $response = new Response();
          $response->setContent($content);

          // Set the response headers.
          $response->headers->set('Content-Type', 'application/pdf');
          $response->headers->set('Content-Disposition', 'attachment; filename="invoice.pdf"');

          // Return the response.
          return $response;
}

public function show_pdf_url(Request $request)
{
    $userId = $request->user()->id;
    $finalId = $request->input('final_id');

    $invoice = DB::table('invoice')->where('final_id', $finalId)->where('user_id', $userId)->first();

    if (!$invoice) {
        return response()->json([ 'success' => false,
            'error' => 'Invoice not found'], 404);
    }

    return response()->json([
        'success' => true,
        'pdf_file_url' => $invoice->pdf_file_url,
    ]);
}



}
