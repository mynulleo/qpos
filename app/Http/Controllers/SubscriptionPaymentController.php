<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Qinvoice;
use App\Models\QinvoiceDetails;
use App\Traits\PaymentTrait;
use App\Traits\SslCommerzTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SubscriptionPaymentController extends Controller
{
    use PaymentTrait, SslCommerzTrait;

    /**
     * Pricing configuration calculation
     *
     * @param int $months
     * @param float $baseRate
     * @return array
     */
    public static function getPlanDetails($months, $baseRate = 500)
    {
        $months = intval($months);
        $baseRate = floatval($baseRate > 0 ? $baseRate : 500);

        $discountPercent = 0;
        if ($months === 6) {
            $discountPercent = 10; // 10% off for 6 months
        } elseif ($months === 12) {
            $discountPercent = 20; // 20% off for 12 months
        }

        $subtotal = $baseRate * $months;
        $discountAmount = ($subtotal * $discountPercent) / 100;
        $totalAmount = $subtotal - $discountAmount;

        return [
            'months' => $months,
            'base_rate' => $baseRate,
            'subtotal' => $subtotal,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Initiate Subscription Payment via Qinvoice and SslCommerzTrait
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'months' => 'required|integer|in:1,2,3,6,12',
        ]);

        $user = Auth::guard('admin')->user() ?? Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized user. Please log in first.',
            ], 401);
        }

        $orgId = $user->organization_id ?? 1;
        $baseFee = 500;
        $orgName = 'My Organization';

        try {
            $org = Organization::find($orgId);
            if ($org) {
                $orgName = $org->organization_name;
                if (!empty($org->subscription_fee)) {
                    $baseFee = floatval($org->subscription_fee);
                }
            }
        } catch (\Exception $e) {}

        // 1. Calculate Plan details
        $plan = self::getPlanDetails($request->months, $baseFee);

        // 2. Create Qinvoice in accessdb.invoices
        try {
            $invoice = Qinvoice::create([
                'organization_id' => $orgId,
                'invoice_no'      => Qinvoice::generateInvoiceNumber(),
                'invoice_date'    => date('Y-m-d'),
                'original_amount' => $plan['subtotal'],
                'discount'        => $plan['discount_amount'],
                'amount'          => $plan['total_amount'],
                'payment_status'  => 'Pending',
                'status'          => 'active',
                'created_by'      => $user->id ?? 1,
                'updated_by'      => $user->id ?? 1,
                'created_ip'      => 1,
                'updated_ip'      => 1,
            ]);

            // 3. Create QinvoiceDetails in accessdb.invoice_details
            QinvoiceDetails::create([
                'invoice_id'      => $invoice->id,
                'account_head_id' => 1,
                'amount'          => $invoice->amount,
                'status'          => 'active',
                'created_by'      => $user->id ?? 1,
                'updated_by'      => $user->id ?? 1,
                'created_ip'      => 1,
                'updated_ip'      => 1,
            ]);

            
        } catch (\Exception $e) {
            Log::error('Failed to create subscription invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Could not create subscription invoice: ' . $e->getMessage(),
            ], 500);
        }

        // 4. Call SslCommerzTrait payment method with invoice_id and month
        $paymentRes = $this->payment($invoice->id, $request->months);

        if (!empty($paymentRes['url'])) {
            return response()->json([
                'success'     => true,
                'gateway_url' => $paymentRes['url'],
                'invoice_id'  => $invoice->id,
                'tran_id'     => $invoice->invoice_no,
                'plan'        => $plan,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $paymentRes['error'] ?? 'No payment gateway redirect URL found.',
        ], 400);
    }

    /**
     * Simulated Payment Gateway Page for Local Testing / Sandbox Fallback
     */
    public function simulate(Request $request)
    {
        $tranId = $request->query('tran_id', 'SUB-' . time());
        $orgId = $request->query('org_id', 1);
        $months = $request->query('months', 1);
        $amount = $request->query('amount', 500);

        return view('admin.subscription.payment_simulation', [
            'tran_id' => $tranId,
            'org_id' => $orgId,
            'months' => $months,
            'amount' => $amount,
        ]);
    }

    /**
     * Re-authenticate admin user into session after returning from SSLCommerz cross-site redirect
     *
     * @param Qinvoice|null $invoice
     * @param int|null $fallbackOrgId
     */
    protected function reAuthenticateUser($invoice = null, $fallbackOrgId = null)
    {
        try {
            $orgId = $invoice ? ($invoice->organization_id ?? null) : $fallbackOrgId;
            if (!$orgId) {
                return;
            }

            $user = null;
            if ($invoice && !empty($invoice->created_by)) {
                $user = \App\Models\OrganizationUser::find($invoice->created_by);
            }
            if (!$user) {
                $user = \App\Models\OrganizationUser::where('organization_id', $orgId)->first();
            }

            if ($user) {
                Auth::guard('admin')->login($user, true);
                session()->put('organization_id', $orgId);
                session()->save();
            }
        } catch (\Exception $e) {
            Log::error('Error re-authenticating user after SSLCommerz return: ' . $e->getMessage());
        }
    }

    /**
     * SSLCommerz Success Callback - Updates Qinvoice and Organization Expire Date
     */
    public function success(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency', 'BDT');
        $bank_tran_id = $request->input('bank_tran_id', $tran_id);
        $card_type = $request->input('card_type', 'SSLCommerz');

        $orgId = $request->input('value_a');
        $months = intval($request->input('value_b', 1));
        $invoiceId = $request->input('value_d');

        // Find invoice in accessdb
        $invoice = null;
        if ($tran_id) {
            $invoice = Qinvoice::where('invoice_no', $tran_id)->first();
        }
        if (!$invoice && $invoiceId) {
            $invoice = Qinvoice::find($invoiceId);
        }

        $newExpiryDate = null;
        if ($invoice) {
            $invoice->update([
                'payment_status' => 'Paid',
                'paid_amount'    => $amount ?: $invoice->amount,
                'payment_date'   => date('Y-m-d H:i:s'),
                'trxid'          => $bank_tran_id,
                'card_type'      => $card_type,
            ]);

            $orgId = $invoice->organization_id;
            if ($orgId && $months) {
                $newExpiryDate = Organization::updateOrganizationExpireDate($orgId, $months);
                Artisan::call('cache:clear');
            }
        } elseif ($orgId && $months) {
            $newExpiryDate = Organization::updateOrganizationExpireDate($orgId, $months);
            Artisan::call('cache:clear');
        }

        // Restore authenticated admin session
        $this->reAuthenticateUser($invoice, $orgId);

        return redirect('/admin/dashboard?payment_status=success&months=' . $months . '&new_expiry=' . urlencode($newExpiryDate ?? ''));
    }

    /**
     * SSLCommerz Fail Callback
     */
    public function fail(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $invoice = null;
        if ($tran_id) {
            $invoice = Qinvoice::where('invoice_no', $tran_id)->first();
            if ($invoice) {
                $invoice->update(['payment_status' => 'Failed']);
            }
        }

        $this->reAuthenticateUser($invoice, $request->input('value_a'));

        return redirect('/admin/dashboard?payment_status=failed&message=' . urlencode('Payment failed or declined. Please try again.'));
    }

    /**
     * SSLCommerz Cancel Callback
     */
    public function cancel(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $invoice = null;
        if ($tran_id) {
            $invoice = Qinvoice::where('invoice_no', $tran_id)->first();
            if ($invoice) {
                $invoice->update(['payment_status' => 'Canceled']);
            }
        }

        $this->reAuthenticateUser($invoice, $request->input('value_a'));

        return redirect('/admin/dashboard?payment_status=canceled&message=' . urlencode('Payment was canceled.'));
    }

    /**
     * SSLCommerz IPN Webhook
     */
    public function ipn(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $months = intval($request->input('value_b', 1));

        if ($tran_id) {
            $invoice = Qinvoice::where('invoice_no', $tran_id)->first();
            if ($invoice) {
                $invoice->update([
                    'payment_status' => 'Paid',
                    'paid_amount'    => $request->input('amount', $invoice->amount),
                    'payment_date'   => date('Y-m-d H:i:s'),
                    'trxid'          => $request->input('bank_tran_id', $tran_id),
                    'card_type'      => $request->input('card_type', 'SSLCommerz'),
                ]);

                Organization::updateOrganizationExpireDate($invoice->organization_id, $months);
                Artisan::call('cache:clear');

                return response()->json(['status' => 'success', 'message' => 'Subscription invoice and organization updated']);
            }
        }

        return response()->json(['status' => 'failed', 'message' => 'Invoice not found'], 404);
    }
}
