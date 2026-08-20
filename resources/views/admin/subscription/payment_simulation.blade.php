<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSLCommerz Payment Checkout - QPOS Subscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ssl-checkout-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            max-width: 480px;
            width: 100%;
        }
        .ssl-header {
            background: #0f172a;
            color: #ffffff;
            padding: 24px;
            text-align: center;
            border-bottom: 3px solid #22c55e;
        }
        .payment-method-btn {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease;
            background: #ffffff;
            cursor: pointer;
            width: 100%;
            margin-bottom: 10px;
        }
        .payment-method-btn:hover {
            border-color: #0f172a;
            background: #f8fafc;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="ssl-checkout-card">
        <div class="ssl-header">
            <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                <i class="fas fa-shield-alt text-success fs-4"></i>
                <h5 class="mb-0 fw-bold">SSLCommerz Secure Checkout</h5>
            </div>
            <small class="text-secondary opacity-75">QPOS Software License & Subscription</small>
        </div>

        <div class="p-4">
            <!-- Order Details -->
            <div class="bg-light p-3 rounded-3 mb-4 border">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-secondary small">Transaction ID:</span>
                    <span class="font-monospace small fw-bold text-dark">{{ $tran_id }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-secondary small">Subscription Plan:</span>
                    <span class="fw-bold text-primary">{{ $months }} Month(s)</span>
                </div>
                <div class="d-flex justify-content-between pt-2 border-top">
                    <span class="fw-bold text-dark">Total Amount:</span>
                    <span class="fw-bold fs-5 text-success">৳ {{ number_format($amount, 2) }} BDT</span>
                </div>
            </div>

            <h6 class="fw-bold text-dark mb-3">Select Payment Method (পেমেন্ট মাধ্যম বেছে নিন):</h6>

            <form action="{{ url('/subscription/payment/success') }}" method="POST">
                @csrf
                <input type="hidden" name="tran_id" value="{{ $tran_id }}">
                <input type="hidden" name="amount" value="{{ $amount }}">
                <input type="hidden" name="value_a" value="{{ $org_id }}">
                <input type="hidden" name="value_b" value="{{ $months }}">
                <input type="hidden" name="status" value="VALID">

                <!-- bKash / Nagad / Cards -->
                <button type="submit" class="payment-method-btn text-dark text-decoration-none">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-danger fs-6 p-2 rounded-3"><i class="fas fa-mobile-alt"></i></span>
                        <div class="text-start">
                            <div class="fw-bold">bKash / Nagad / Rocket</div>
                            <small class="text-muted">Instant Mobile Banking</small>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-muted"></i>
                </button>

                <button type="submit" class="payment-method-btn text-dark text-decoration-none">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-primary fs-6 p-2 rounded-3"><i class="fas fa-credit-card"></i></span>
                        <div class="text-start">
                            <div class="fw-bold">Visa / MasterCard / AMEX</div>
                            <small class="text-muted">Debit & Credit Cards</small>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-muted"></i>
                </button>

                <button type="submit" class="payment-method-btn text-dark text-decoration-none">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-success fs-6 p-2 rounded-3"><i class="fas fa-university"></i></span>
                        <div class="text-start">
                            <div class="fw-bold">Internet Banking / Nexus</div>
                            <small class="text-muted">City Touch, Islami Bank, DBBL</small>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-muted"></i>
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="{{ url('/subscription/payment/cancel?tran_id=' . $tran_id) }}" class="text-secondary small text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Cancel and return to software
                </a>
            </div>
        </div>
    </div>
</body>
</html>
