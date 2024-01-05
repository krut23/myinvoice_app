<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Invoice</title>

    <style>
         .cs-signature {
           float: right;
           width:1%;
           height:1%;
            }
        .cs-invoice_btn {
            display: -webkit-inline-box;
            display: -ms-inline-flexbox;
            display: inline-flex;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            border: none;
            font-weight: 600;
            padding: 8px 20px;
            cursor: pointer;
        }

        .cs-invoice_btn svg {
            width: 24px;
            margin-right: 5px;
        }

        .cs-invoice_btn.cs-color1 {
            color: #111111;
            background: rgba(42, 209, 157, 0.15);
        }

        .cs-invoice_btn.cs-color1:hover {
            background-color: rgba(42, 209, 157, 0.3);
        }

        .cs-invoice_btn.cs-color2 {
            color: #fff;
            background: #2ad19d;
        }

        .cs-invoice_btn.cs-color2:hover {
            background-color: rgba(42, 209, 157, 0.8);
        }

        @media print {
            .cs-hide_print {
                display: none !important;
            }
        }

        .cs-invoice_btns {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            margin-top: 30px;
        }

        .cs-invoice_btns .cs-invoice_btn:first-child {
            border-radius: 5px 0 0 5px;
        }

        .cs-invoice_btns .cs-invoice_btn:last-child {
            border-radius: 0 5px 5px 0;
        }

        .invoice-box {
            width: 150mm; /* Adjust the width as needed */
            height: 210mm; /* Adjust the height as needed */
            margin: 0 auto;
            padding: 10mm;
            border: 1px solid #352f2f;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 14px; /* Adjust the font size as needed */
            line-height: 20px; /* Adjust the line height as needed */
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 3px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 10px; /* Adjust the padding as needed */
        }

        .invoice-box table tr.top table td.title {
            font-size: 30px; /* Adjust the font size as needed */
            line-height: 30px; /* Adjust the line height as needed */
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.heading td {
            background: #dbdbdb; 
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 10px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        /** RTL **/
        .invoice-box.rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .invoice-box.rtl table {
            text-align: right;
        }

        .invoice-box.rtl table tr td:nth-child(2) {
            text-align: left;
        }

        /* Style for the left-aligned "Total" class table */
        .invoice-box table.left-total-table {
            width: 50%;
            float: right;
        }

        .cs-invoice.cs-style1 .cs-invoice_left {
            max-width: 55%;
            }
        .cs-primary_color {
            color: #111111;
            }
        .cs-focus_bg {
            background: #f6f6f6;
            }
        .cs-text_right {
            text-align: right;
            }
         .cs-invoice.cs-style1 .cs-invoice_head .cs-text_right {
                text-align: left;
            }

            .cs-width_1 {
            width: 8.33333333%;
            }

            .cs-width_2 {
            width: 16.66666667%;
            }

            .cs-width_3 {
            width: 25%;
            }

            .cs-width_4 {
            width: 33.33333333%;
            }

            .cs-semi_bold {
            font-weight: 600;
            }
            .info {
        padding-left: 10px; /* Adjust the spacing as needed */
        text-align: center;
    }

    .business-info {
        font-size: 16px;
    }

    .business-info strong {
        font-size: 20px; /* You can adjust the size for the business name */
    }

    .business-info p {
        margin: 0;
        padding: 0;
        font-size: 14px;
    }
    .centered-text {
        text-align: center;
        margin: 0;
        padding: 0;
        font-size: 14px;
    }
    .cs-signature {
    float: right;
    width:1%;
    height:1%;
            }
            .background{
                background: #e9e9e9;
            }
        @page{
            size:210mm 297mm;
            margin:27mm 16mm 27mm 16mm;
        }
    </style>
</head>

<body>
<div class="invoice-box">
    <table cellpadding="0" cellspacing="0">
        <tr class="top">
            <td colspan="2">
                <table>
                    <tr>
                        <td class="title">
                            <img
                                src="{{ $business_logo }}"
                                style="width: 50%; max-width: 70px"
                            />
                        </td>
                        <td class="info">
                            <div class="business-info">
                                <p class="centered-text"><strong>{{$users->business_name}}</strong></p>
                                <p class="centered-text">{{ $users->address }}</p>
                                <p class="centered-text"><b>Mobile:</b>&nbsp;{{ $users->phone_number }}</p>
                                <p class="centered-text"><b>GSTIN:</b>&nbsp;{{ $users->gst_number }}</p>
                            </div>
                        </td>

                    </tr>
                </table>
            </td>
        </tr>
        <tr class="heading">
            <td>Invoice No:{{ substr($invoice->final_id, 0, 17) }}</td>

            <td>Invoice Date: {{ $invoice->invoice_date }}</td>
        </tr>
        <tr class="information">
            <td colspan="2">
                <table>
                    <tr>
                        <td class="cs-invoice_left" style="width: 100px; padding: 2px;">
                            <b class="cs-primary_color">BILL TO</b>
                            <br>
                            {{ $invoice->customer_name }}<br>
                            {{ $invoice->billing_address }}<br>
                            {{ $invoice->gst_number }}
                         </td>

                         <td class="cs-invoice_right cs-text_right" style="width: 100px; padding: 2px;">
                            <b class="cs-primary_color">SHIP TO</b>
                            <br>
                            {{ $invoice->customer_name }}<br>
                            {{ $invoice->billing_address }}
                         </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table>
        <thead>
           <tr >
              <th class="cs-width_3 cs-semi_bold cs-primary_color cs-focus_bg background" style="width: 100px; padding: 5px;">ITEM</th>
              <th class="cs-width_4 cs-semi_bold cs-primary_color cs-focus_bg background" style="width: 100px; padding: 5px;">QTY.</th>
              <th class="cs-width_2 cs-semi_bold cs-primary_color cs-focus_bg background" style="width: 100px; padding: 5px;">RATE</th>
              <th class="cs-width_1 cs-semi_bold cs-primary_color cs-focus_bg background" style="width: 100px; padding: 5px;">TAX</th>
              <th class="cs-width_2 cs-semi_bold cs-primary_color cs-focus_bg background cs-text_right" style="width: 100px; padding: 5px;">AMOUNT</th>
           </tr>
        </thead>
        <tbody>
           @foreach ($invoice_item_data as $invoice_item)
           <tr>
              <td class="cs-width_3" style="width: 100px; padding: 5px;">{{ $invoice_item->item_name }}</td>
              <td class="cs-width_4" style="width: 100px; padding: 5px;">{{ $invoice_item->item_qty }}</td>
              <td class="cs-width_2" style="width: 100px; padding: 5px;">{{ $invoice_item ->sales_price }}</td>
              <td class="cs-width_1" style="width: 100px; padding: 5px;">{{ $invoice_item->total_tax }}</td>
              <td class="cs-width_2 cs-text_right" style="width: 100px; padding: 5px;">{{ $invoice_item->total_sales_price }}</td>
           </tr>
           @endforeach

           <tr style="width: 150px; padding: 5px;">
              <td class="cs-width_3 cs-semi_bold cs-primary_color cs-focus_bg background" style="width: 100px; padding: 5px;"><strong>SUB TOTAL</strong></td>
              <td class="cs-width_2 cs-focus_bg background" style="width: 100px; padding: 5px;"><strong>{{ $totalQuantity }}</strong></td>
              <td class="cs-width_1 cs-focus_bg background" style="width: 100px; padding: 5px;"><strong>{{ $totalRate }}</strong></td>
              <td class="cs-width_2 cs-focus_bg background" style="width: 100px; padding: 5px;"><strong>{{ $totalTax }}</strong></td>
              <td class="cs-width_2 cs-text_right cs-focus_bg background" style="width: 100px; padding: 5px;"><strong>{{ $totalAmount }}</strong></td>
           </tr>
        </tbody>
     </table>
    <!-- Separate table for "Total" class rows on the left side -->
    <table class="left-total-table">
        <tr class="total" >
            <td class="cs-width_3 cs-semi_bold cs-primary_color cs-focus_bg background" style="width: 200px;">TAXTABLE AMOUNT</td>
            <td class="cs-width_3  cs-primary_color cs-focus_bg background" style="width: 100px;"> {{ $totalAmount }}</td>
        </tr>
        <tr class="total">
            <td class="cs-width_3 cs-semi_bold cs-primary_color cs-focus_bg background">IGST@5.0%</td>
            <td class="cs-width_3  cs-primary_color cs-focus_bg background" >{{ $totalTax }}</td>
        </tr>
        <tr class="total">
            <td class="cs-width_3 cs-semi_bold cs-primary_color cs-focus_bg background">Additional Charge</td>
            <td class="cs-width_3  cs-primary_color cs-focus_bg background" >{{ $invoice->additional_charge }}</td>
        </tr>
        <tr class="total">
            <td class="cs-width_3 cs-semi_bold cs-primary_color cs-focus_bg background">Discount</td>
            <td class="cs-width_3  cs-primary_color cs-focus_bg background">{{ $invoice->discount }}</td>
        </tr>
        <tr class="total">
            <td class="cs-width_3 cs-semi_bold cs-primary_color cs-focus_bg background">Round Off </td>
            <td class="cs-width_3  cs-primary_color cs-focus_bg background">{{ $invoice->round_off }}</td>
        </tr>
        <tr class="total">
            <td class="cs-width_3 cs-semi_bold cs-primary_color cs-focus_bg background">GRAND TOTAL</td>
            <td class="cs-width_3  cs-primary_color cs-focus_bg background">{{ $totalAmount }}</td>
        </tr>
        <tr class="total">
            <td class="cs-width_3 cs-semi_bold cs-primary_color cs-focus_bg background">Received Amount</td>
            <td class="cs-width_3  cs-primary_color cs-focus_bg background">{{ $totalAmount }}</td>

        </tr>
        <tr class="total">
            <td class="cs-width_3 cs-semi_bold cs-primary_color cs-focus_bg background">Balance</td>
            <td class="cs-width_3 cs-primary_color cs-focus_bg background">{{ $invoice->balance_amount }}</td>
        </tr>
        <tr class="total">
            <td class="cs-width_3 cs-semi_bold cs-primary_color cs-focus_bg background">Invoice Amount (in words)</td>
            <td class="cs-width_3  cs-primary_color cs-focus_bg background" style="font-size: 10px;">{{ $word}}</td>
         </tr>
         <tr>
            <td></td>
            <td>
              <img src="{{ asset('/storage/signatures/' . $signature) }}" alt="Signature Image", style="width: 50px; background: #e9e9e9;">
            </td>
          </tr>
    </table>
    {{-- <div style="float:right">
        <img src="{{ asset('/storage/signatures/' . $signature) }}" alt="Signature" style="width: 50px; height: 25px; margin-top: 300px; float:right" />
     </div> --}}
</div>

<!-- Print and Download buttons -->
<!--<div class="cs-invoice_btns cs-hide_print">-->
<!--    <a href="javascript:window.print()" class="cs-invoice_btn cs-color1">-->
<!--       <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">-->
<!--          <path d="M384 176h40a40 40 0 0140 40v208a40 40 0 01-40 40H136a40 40 0 01-40-40V216a40 40 0 0140-40h40" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32"/>-->
<!--          <rect x="128" y="240" width="256" height="208" rx="24.32" ry="24.32" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32"/>-->
<!--          <path d="M384 128v-24a40.12 40.12 0 00-40-40H168a40.12 40.12 0 00-40 40v24" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32"/>-->
<!--          <circle cx="392" cy="184" r="24"/>-->
<!--       </svg>-->
<!--       <span>Print</span>-->
<!--    </a>-->
<!--    <a href="{{ route('invoice.download', $invoice->final_id) }}" class="btn btn-primary">Download PDF</a>-->

<!--</div>-->
</body>
</html>
