<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    body { font-family: 'Segoe UI', Arial, sans-serif; background:#f4f7f0; margin:0; padding:0; }
    .wrapper { max-width:580px; margin:40px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.07); }
    .header { background:#86B817; padding:28px 32px; }
    .header h1 { color:#fff; margin:0; font-size:24px; }
    .header p { color:rgba(255,255,255,.85); margin:4px 0 0; font-size:14px; }
    .body { padding:32px 40px; }
    .product-card { background:#f8fdf0; border-left:4px solid #86B817; border-radius:6px; padding:16px 20px; margin:20px 0; }
    .badge { display:inline-block; background:#fff3cd; color:#856404; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; }
    .btn { display:inline-block; background:#86B817; color:#fff; padding:12px 28px; border-radius:25px; text-decoration:none; font-weight:600; font-size:15px; margin-top:20px; }
    .footer { background:#f4f7f0; padding:16px 32px; text-align:center; font-size:12px; color:#888; }
    .meta { color:#666; font-size:13px; line-height:1.7; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>🍎 FruGo — Admin Review Required</h1>
        <p>A product has been submitted for your approval</p>
    </div>
    <div class="body">
        <p style="color:#333;font-size:16px;margin:0 0 4px;">Hello Admin,</p>
        <p style="color:#555;line-height:1.6;margin:0 0 16px;">
            <strong>{{ $submittedBy->name }}</strong> ({{ $submittedBy->email }}) has submitted a new product for approval:
        </p>

        <div class="product-card">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <div style="flex:1;">
                    <h3 style="margin:0 0 4px;color:#222;font-size:18px;">{{ $product->title }}</h3>
                    <span class="badge">{{ ucfirst($product->category ?? 'General') }}</span>
                </div>
                <div style="font-size:22px;font-weight:700;color:#86B817;">${{ number_format($product->price, 2) }}</div>
            </div>
            <div class="meta">
                <strong>Description:</strong> {{ Str::limit(strip_tags($product->description), 150) }}<br>
                <strong>Stock:</strong> {{ $product->stock }} units<br>
                <strong>Available from:</strong> {{ $product->date_available->format('d M Y') }}<br>
                <strong>Submitted by:</strong> {{ $submittedBy->name }} on {{ now()->format('d M Y, g:i A') }}
            </div>
        </div>

        <p style="color:#555;font-size:14px;line-height:1.6;">
            Please review this product and approve or reject it with feedback from your <strong>Admin Dashboard</strong>.
        </p>

        <a href="{{ config('app.url') }}/dashboard" class="btn">Review in Dashboard →</a>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} FruGo &mdash; This is an automated notification.
    </div>
</div>
</body>
</html>
