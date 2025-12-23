<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Invoice #{{ $payment->id }}</title>
  <style>
    body{font-family:DejaVu Sans, sans-serif; font-size:14px; color:#333;}
    .header{text-align:center; margin-bottom:20px;}
    .header h1{margin:0; font-size:22px; color:#16a34a;}
    .table{width:100%; border-collapse:collapse; margin-top:20px;}
    .table th,.table td{border:1px solid #ddd; padding:8px; text-align:left;}
    .table th{background:#f3f4f6;}
    .footer{text-align:center; margin-top:40px; font-size:12px; color:#555;}
  </style>
</head>
<body>
  <div class="header">
    <h1>Invoice</h1>
    <p>Payment Receipt</p>
  </div>

  <table class="table">
    <tr>
      <th>Invoice ID</th>
      <td>#{{ $payment->id }}</td>
    </tr>
    <tr>
      <th>Transaction</th>
      <td>{{ $payment->transaction_id }}</td>
    </tr>
    <tr>
      <th>Amount</th>
      <td>{{ number_format($payment->amount,2) }} {{ $payment->currency }}</td>
    </tr>
    <tr>
      <th>Status</th>
      <td>{{ ucfirst($payment->status) }}</td>
    </tr>
    <tr>
      <th>Date</th>
      <td>{{ $payment->created_at->format('d M Y, H:i A') }}</td>
    </tr>
  </table>

  <div class="footer">
    <p>Thank you for your payment.</p>
  </div>
</body>
</html>
