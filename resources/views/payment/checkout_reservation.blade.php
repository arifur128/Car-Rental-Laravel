<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reservation Checkout – Card (Fake)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Same CSS as above --}}
  <style>
    :root{--bg:#0f172a;--card:#111827;--muted:#64748b;--text:#e5e7eb;--accent:#2563eb;--ok:#16a34a;--err:#dc2626;--ring:rgba(37,99,235,.35)}
    *{box-sizing:border-box} body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu;background:linear-gradient(140deg,#0b1225,#0d142c 40%,#0b1225);color:var(--text)}
    .wrap{max-width:1100px;margin:32px auto;padding:0 16px}.grid{display:grid;grid-template-columns:1.35fr .9fr;gap:24px}@media (max-width:900px){.grid{grid-template-columns:1fr}}
    .panel{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:20px;box-shadow:0 10px 30px rgba(0,0,0,.25)}
    h1{font-size:24px;margin:0 0 8px}.sub{color:var(--muted);margin:0 0 14px;font-size:14px}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}.row3{display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px}
    .field{display:flex;flex-direction:column;gap:6px}label{font-size:13px;color:#cbd5e1}
    input,select{background:#0b1020;border:1px solid rgba(255,255,255,.1);color:var(--text);padding:12px;border-radius:10px;outline:none;transition:.15s}
    input:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 4px var(--ring)}.help{font-size:12px;color:var(--muted)}.error{color:var(--err);font-size:12px}
    .paybtn{display:inline-flex;align-items:center;gap:10px;padding:12px 16px;background:var(--accent);border:0;color:#fff;border-radius:12px;cursor:pointer;font-weight:600;margin-top:6px}
    .paybtn:hover{filter:brightness(1.05)}.badge{font-size:12px;padding:2px 8px;border-radius:999px;background:#0b1020;border:1px solid rgba(255,255,255,.1);color:#cbd5e1}
    .card-preview{position:relative;height:200px;border-radius:18px;background:
      radial-gradient(1200px 600px at -10% -40%, #1d4ed8 10%, transparent 60%),
      radial-gradient(900px 500px at 120% 140%, #7c3aed 5%, transparent 60%),
      linear-gradient(135deg,#0b1020,#10162a);border:1px solid rgba(255,255,255,.15);color:#eaf2ff;padding:18px;overflow:hidden}
    .brand{position:absolute;right:16px;top:16px;font-weight:700;letter-spacing:.5px}
    .chip{width:44px;height:32px;background:linear-gradient(180deg,#e5e7eb,#94a3b8);border-radius:6px;margin-top:8px}
    .num{margin-top:22px;font-size:22px;letter-spacing:2px}.meta{display:flex;justify-content:space-between;margin-top:18px;font-size:12px;color:#c7d2fe}
    .name{font-size:14px;margin-top:10px;text-transform:uppercase;letter-spacing:1.2px}
    .summary h2{margin:0 0 10px;font-size:18px}.summary .line{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px dashed rgba(255,255,255,.1)}
    .total{display:flex;justify-content:space-between;padding-top:12px;font-weight:700;font-size:18px}.pill{padding:4px 10px;border:1px solid rgba(255,255,255,.12);border-radius:999px;color:#cbd5e1;font-size:12px}
  </style>
</head>
<body>
<div class="wrap">
  <h1>Checkout Reservation #{{ $reservation->id }}</h1>
  <p class="sub">Car: <span class="badge">{{ $reservation->car->name ?? 'N/A' }}</span> • Paying with card (fake)</p>

  @if ($errors->any())
    <div class="panel" style="border-color:rgba(220,38,38,.35); background:rgba(220,38,38,.08);">
      <ul style="margin:0 0 0 14px;">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('checkout.reservation.process', $reservation->id) }}" method="post" class="grid">
    @csrf

    <div class="panel">
      <div class="card-preview" id="cardPreview">
        <div class="brand" id="brand">CARD</div>
        <div class="chip"></div>
        <div class="num" id="pvNumber">•••• •••• •••• ••••</div>
        <div class="name" id="pvName">{{ (auth()->user()->name ?? 'CARD HOLDER') }}</div>
        <div class="meta">
          <div>EXPIRES<br><strong id="pvExp">MM/YY</strong></div>
          <div>CVV<br><strong id="pvCvv">•••</strong></div>
        </div>
      </div>

      <div style="height:12px"></div>
      <div class="row">
        <div class="field">
          <label>Cardholder Name</label>
          <input type="text" name="card_name" id="card_name" placeholder="e.g., Mahmudul Hasan" autocomplete="cc-name" required value="{{ auth()->user()->name ?? '' }}">
          <div class="help">Exactly as printed on card</div>
        </div>
        <div class="field">
          <label>Currency</label>
          <input type="text" readonly value="{{ $currency }}" style="opacity:.8">
          <input type="hidden" name="currency" value="{{ $currency }}">
        </div>
      </div>

      <div class="field">
        <label>Card Number</label>
        <input type="text" inputmode="numeric" name="card_number" id="card_number" maxlength="19" placeholder="0000 0000 0000 0000" autocomplete="cc-number" required>
        <div class="help">We’ll auto-space the digits</div>
      </div>

      <div class="row3">
        <div class="field">
          <label>Expiry</label>
          <input type="text" inputmode="numeric" name="card_expiry" id="card_expiry" maxlength="5" placeholder="MM/YY" autocomplete="cc-exp" required>
        </div>
        <div class="field">
          <label>CVV</label>
          <input type="password" inputmode="numeric" name="card_cvv" id="card_cvv" maxlength="4" placeholder="•••" autocomplete="cc-csc" required>
        </div>
        <div class="field">
          <label>Amount</label>
          <input type="text" readonly value="{{ number_format($amount,2) }}">
          <input type="hidden" name="amount" value="{{ $amount }}">
        </div>
      </div>

      <div class="field">
        <label>Note (optional)</label>
        <input type="text" name="note" placeholder="e.g., Pay for reservation #{{ $reservation->id }}">
      </div>

      <button class="paybtn" type="submit">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M2 7h20M2 11h20M6 15h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
        Pay Now (Fake)
      </button>
      <p class="help" style="margin-top:8px">No real charge—demo only.</p>
    </div>

    <div class="panel summary">
      <h2>Order Summary</h2>
      <div class="line"><span>Reservation</span><span class="pill">#{{ $reservation->id }}</span></div>
      <div class="line"><span>Car</span><span>{{ $reservation->car->name ?? 'N/A' }}</span></div>
      <div class="line"><span>Gateway</span><span>Fake</span></div>
      <div class="total"><span>Total</span><span>{{ $currency==='USD'?'$':'৳' }} {{ number_format($amount,2) }}</span></div>
      <p class="help" style="margin-top:8px">Amounts are locked to this reservation.</p>
    </div>
  </form>
</div>

<script>
  const el = id=>document.getElementById(id);
  const number = el('card_number'), pvNumber = el('pvNumber'), brand = el('brand');
  number.addEventListener('input', e=>{
    let v = e.target.value.replace(/\D/g,'').slice(0,16);
    const groups = v.match(/.{1,4}/g) || [];
    const spaced = groups.join(' ');
    e.target.value = spaced;
    pvNumber.textContent = spaced.padEnd(19,'•').replace(/ /g,' ');
    if(/^4/.test(v)) brand.textContent = 'VISA';
    else if(/^5[1-5]/.test(v)) brand.textContent = 'MASTERCARD';
    else brand.textContent = 'CARD';
  });
  const nameIn = el('card_name'), pvName = el('pvName');
  nameIn.addEventListener('input', e=>{
    const txt = e.target.value.trim().toUpperCase();
    pvName.textContent = txt || 'CARD HOLDER';
  });
  const exp = el('card_expiry'), pvExp = el('pvExp');
  exp.addEventListener('input', e=>{
    let v = e.target.value.replace(/\D/g,'').slice(0,4);
    if(v.length>=3) v = v.slice(0,2) + '/' + v.slice(2);
    e.target.value = v; pvExp.textContent = v || 'MM/YY';
  });
  const cvv = el('card_cvv'), pvCvv = el('pvCvv');
  cvv.addEventListener('input', e=>{
    let v = e.target.value.replace(/\D/g,'').slice(0,4);
    e.target.value = v; pvCvv.textContent = v.replace(/./g,'•') || '•••';
  });
</script>
</body>
</html>
