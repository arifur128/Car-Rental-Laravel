<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Payment Success</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Bootstrap CSS --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="card shadow-lg border-success">
          <div class="card-header bg-success text-white text-center">
            <h3 class="mb-0">✅ Payment Successful</h3>
          </div>
          <div class="card-body text-center">
            
            @if($payment)
              <p class="text-muted">Your payment has been processed successfully!</p>

              <table class="table table-bordered mt-3">
                <tbody>
                  <tr>
                    <th class="text-start">Payment ID</th>
                    <td class="text-start">{{ $payment->id }}</td>
                  </tr>
                  <tr>
                    <th class="text-start">Transaction</th>
                    <td class="text-start">{{ $payment->transaction_id }}</td>
                  </tr>
                  <tr>
                    <th class="text-start">Amount</th>
                    <td class="text-start">{{ number_format($payment->amount,2) }} {{ $payment->currency }}</td>
                  </tr>
                  <tr>
                    <th class="text-start">Status</th>
                    <td class="text-start">
                      <span class="badge bg-success">{{ ucfirst($payment->status) }}</span>
                    </td>
                  </tr>
                  <tr>
                    <th class="text-start">Date</th>
                    <td class="text-start">{{ $payment->created_at->format('d M Y, H:i A') }}</td>
                  </tr>
                </tbody>
              </table>

              {{-- Download Invoice --}}
              <a href="{{ route('payment.invoice', $payment->id) }}" 
                 class="btn btn-success mt-3" target="_blank">
                ⬇️ Download Invoice (PDF)
              </a>
            @else
              <div class="alert alert-warning mt-3">
                Payment details not found in session.
              </div>
            @endif

            {{-- Navigation Buttons --}}
            <div class="mt-4 d-flex justify-content-center gap-2">
              <a href="{{ route('checkout') }}" class="btn btn-outline-success">
                ← Back to Checkout
              </a>
              <a href="{{ route('home') }}" class="btn btn-primary">
                🏠 Back to Home
              </a>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Bootstrap JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
