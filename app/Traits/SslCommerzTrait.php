<?php

namespace App\Traits;

use Exception;
use App\Models\Qinvoice;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Library\SslCommerz\SslCommerzNotification;

trait SslCommerzTrait
{
    /**
     * Prepare SSLCommerz payment for an invoice
     *
     * @param int $invoice_id
     * @param int|null $month
     * @return array
     */
    public function payment($invoice_id, $month = null)
    {
        // Try to load Qinvoice from accessdb first, fallback to tenant Invoice
        $invoice = Qinvoice::with('organization', 'invoice_details')->find($invoice_id);
        if (!$invoice) {
            $invoice = Invoice::with('residence', 'invoice_details')->find($invoice_id);
        }
        
        if (empty($invoice)) {
            return [
                'url' => '',
                'error' => "Sorry Invoice doesn't match our records"
            ];
        }

        $org = $invoice->organization ?? null;
        $user = auth('admin')->user() ?? auth()->user();

        $name = $org->organization_name ?? '';
        $email = $org->email ?? '';
        $phone = $org->mobile ?? '';
        $address = $org->address ?? ($invoice->address ?? 'Dhaka');
        $pro = "Software Subscription - " . ($month ? "{$month} Month(s)" : "Renewal");
        $tranID = $invoice->invoice_no;

        $post_data = [];
        $post_data['total_amount'] = (float)$invoice->amount;
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = (string)$tranID;

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $name ?: 'Customer';
        $post_data['cus_email'] = $email ?: 'admin@qpos.com';
        $post_data['cus_add1'] = $address ?: 'Dhaka';
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "Dhaka";
        $post_data['cus_state'] = "Dhaka";
        $post_data['cus_postcode'] = "1000";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = $phone ?: '01912252095';
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = $name ?: 'Customer';
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = $phone ?: '01912252095';
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = $pro;
        $post_data['product_category'] = "Software Subscription";
        $post_data['product_profile'] = "non-physical-goods";

        # OPTIONAL / CALLBACK PARAMETERS
        $post_data['value_a'] = (string)($invoice->organization_id ?? 1);
        $post_data['value_b'] = (string)($month ?? 1);
        $post_data['value_c'] = (string)$invoice->amount;
        $post_data['value_d'] = (string)$invoice->id;

        $data = [
            'url' => '',
            'error' => ''
        ];

        $sslc = new SslCommerzNotification();
        $payment_options = $sslc->makePayment($post_data, 'hosted');

        $data = [
            'url' => '',
            'error' => ''
        ];

        if ($payment_options) {
            $data['url'] = $payment_options;
        } else {
            $data['error'] = 'No redirect url found';
        }

        return $data;
    }
}
