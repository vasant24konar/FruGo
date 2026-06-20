@extends('layouts.shop')
@section('title', 'Order #' . str_pad($order->id, 4, '0', STR_PAD_LEFT))

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Order #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</h2>
        <a href="{{ route('shop.orders') }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i>Back to Orders
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Order Items</h5>
                    <table class="table">
                        <thead class="table-light">
                            <tr><th>Product</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Subtotal</th></tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product?->title ?? 'Deleted product' }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end fw-bold">${{ number_format($item->subtotal(), 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total</td>
                                <td class="text-end fw-bold fs-5" style="color:var(--bs-primary)">${{ number_format($order->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Order Info</h5>
                    <dl class="row small mb-0">
                        <dt class="col-5 text-muted">Status</dt>
                        <dd class="col-7">
                            <span class="badge bg-{{ $order->getStatusBadgeClass() }}">{{ ucfirst($order->status) }}</span>
                        </dd>
                        <dt class="col-5 text-muted">Date</dt>
                        <dd class="col-7">{{ $order->created_at->format('d M Y, H:i') }}</dd>
                        <dt class="col-5 text-muted">Ship to</dt>
                        <dd class="col-7">{{ $order->shipping_name }}</dd>
                        <dt class="col-5 text-muted">Address</dt>
                        <dd class="col-7">{{ $order->shipping_address }}, {{ $order->shipping_city }}</dd>
                        <dt class="col-5 text-muted">Phone</dt>
                        <dd class="col-7">{{ $order->shipping_phone }}</dd>
                        @if($order->notes)
                        <dt class="col-5 text-muted">Notes</dt>
                        <dd class="col-7">{{ $order->notes }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
