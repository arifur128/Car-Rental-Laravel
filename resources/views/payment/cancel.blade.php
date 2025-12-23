<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Payment Failed/Cancelled</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="p-6">
  <h1 style="color:#dc2626;">❌ Payment Failed/Cancelled</h1>

  @if($payment)
    <ul>
      <li><strong>ID:</strong> {{ $payment->id }}</li>
      <li><strong>Txn:</strong> {{ $payment->transaction_id }}</li>
      <li><strong>Amount:</strong> {{ number_format($payment->amount,2) }} {{ $payment->currency }}</li>
      <li><strong>Status:</strong> {{ ucfirst($payment->status) }}</li>
      <li><strong>Created:</strong> {{ $payment->created_at }}</li>
    </ul>
  @else
    <p>Payment details not found in session.</p>
  @endif

  <p class="mt-4"><a href="{{ route('checkout') }}">← Try again</a></p>
</body>
</html>
