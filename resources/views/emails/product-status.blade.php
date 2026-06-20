<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    body { font-family: 'Segoe UI', Arial, sans-serif; background:#f4f7f0; margin:0; padding:0; }
    .wrapper { max-width:580px; margin:40px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.07); }
    .header-approved { background:#86B817; padding:28px 32px; }
    .header-rejected { background:#dc3545; padding:28px 32px; }
    .header h1 { color:#fff; margin:0; font-size:22px; }
    .header p { color:rgba(255,255,255,.85); margin:4px 0 0; font-size:14px; }
    .body { padding:32px 40px; }
    .product-card { background:#f8f9fa; border-radius:8px; padding:16px 20px; margin:20px 0; border:1px solid #e9ecef; }
    .reason-box { background:#fff3f3; border-left:4px solid #dc3545; border-radius:6px; padding:14px 18px; margin:16px 0; }
    .btn-green { display:inline-block; background:#86B817; color:#fff; padding:12px 28px; border-radius:25px; text-decoration:none; font-weight:600; font-size:15px; margin-top:16px; }
    .btn-red { display:inline-block; background:#dc3545; color:#fff; padding:12px 28px; border-radius:25px; text-decoration:none; font-weight:600; font-size:15px; margin-top:16px; }
    .footer { background:#f4f7f0; padding:16px 32px; text-align:center; font-size:12px; color:#888; }
</style>
</head>
<body>
<div class="wrapper">
    @if($status === 'approved')
    <div class="header header-approved">
        <h1>✅ Product Approved!</h1>
        <p>Your product is now live on FruGo</p>
    </div>
    <div class="body">
        <p style="color:#333;font-size:16px;margin:0 0 4px;">Congratulations!</p>
        <p style="color:#555;line-height:1.6;">
            Your product <strong>"{{ $product->title }}"</strong> has been <strong style="color:#86B817;">approved</strong> and is now visible to customers in the FruGo shop.
        </p>

        <div class="product-card">
            <h3 style="margin:0 0 6px;color:#222;">{{ $product->title }}</h3>
            <p style="margin:0;color:#666;font-size:13px;">
                Category: <strong>{{ ucfirst($product->category ?? 'General') }}</strong> &nbsp;|&nbsp;
                Price: <strong>${{ number_format($product->price, 2) }}</strong> &nbsp;|&nbsp;
                Stock: <strong>{{ $product->stock }} units</strong>
            </p>
        </div>

        <a href="{{ config('app.url') }}/products/{{ $product->id }}" class="btn-green">View Product →</a>
    </div>
    @else
    <div class="header header-rejected">
        <h1>❌ Product Needs Revision</h1>
        <p>Your product requires changes before it can be approved</p>
    </div>
    <div class="body">
        <p style="color:#333;font-size:16px;margin:0 0 4px;">Hi there,</p>
        <p style="color:#555;line-height:1.6;">
            Your product <strong>"{{ $product->title }}"</strong> could not be approved at this time.
            Please review the feedback below, make the necessary changes, and resubmit.
        </p>

        <div class="product-card">
            <h3 style="margin:0 0 6px;color:#222;">{{ $product->title }}</h3>
            <p style="margin:0;color:#666;font-size:13px;">
                Category: <strong>{{ ucfirst($product->category ?? 'General') }}</strong> &nbsp;|&nbsp;
                Price: <strong>${{ number_format($product->price, 2) }}</strong>
            </p>
        </div>

        @if($reason)
        <h4 style="color:#dc3545;margin:16px 0 8px;font-size:15px;">Admin Feedback:</h4>
        <div class="reason-box">
            <p style="margin:0;color:#721c24;line-height:1.6;">{{ $reason }}</p>
        </div>
        @endif

        <p style="color:#555;font-size:14px;line-height:1.6;margin-top:16px;">
            Please update your product based on the feedback and resubmit for review from your Products dashboard.
        </p>

        <a href="{{ config('app.url') }}/products/{{ $product->id }}/edit" class="btn-red">Update &amp; Resubmit →</a>
    </div>
    @endif
    <div class="footer">
        &copy; {{ date('Y') }} FruGo &mdash; This is an automated notification.
    </div>
</div>
</body>
</html>
