@extends('layouts.shop')
@section('title', $product->title)

@section('content')
<div class="container py-5">

    <div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <a href="{{ route('products.index') }}" class="text-decoration-none" style="color:var(--bs-primary)">
            <i class="fa fa-arrow-left me-1"></i>All Products
        </a>
        @if(auth()->user()->isAdmin() || $product->created_by === auth()->id())
        <div class="d-flex gap-2">
            <a href="{{ route('products.edit', $product) }}"
               class="btn btn-sm fw-semibold" style="background:var(--bs-primary);color:#fff">
                <i class="fa fa-edit me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('products.destroy', $product) }}"
                  onsubmit="return confirm('Permanently delete this product?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fa fa-trash me-1"></i>Delete
                </button>
            </form>
        </div>
        @endif
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <img src="{{ $product->image_path ? asset($product->image_path) : asset('img/fruite-item-1.jpg') }}"
                 class="img-fluid rounded-3 shadow-sm w-100"
                 style="object-fit:cover;height:280px"
                 alt="{{ $product->title }}">
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100" style="border-radius:.85rem">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <h2 class="fw-bold mb-0" style="font-family:'Raleway',sans-serif">{{ $product->title }}</h2>
                        @if($product->category)
                            <span class="badge ms-2 flex-shrink-0" style="background:var(--bs-primary);font-size:.78rem">{{ ucfirst($product->category) }}</span>
                        @endif
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-4">
                            <div class="text-muted small mb-1">Price</div>
                            <div class="fw-bold fs-5" style="color:var(--bs-primary)">{{ $product->currency === 'INR' ? '₹' : '$' }}{{ number_format((float)$product->price, 2) }}</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small mb-1">Stock</div>
                            @if($product->stock == 0)
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($product->stock < 10)
                                <span class="badge bg-warning text-dark">{{ $product->stock }} left</span>
                            @else
                                <div class="fw-semibold">{{ $product->stock }} units</div>
                            @endif
                        </div>
                        <div class="col-4">
                            <div class="text-muted small mb-1">Available From</div>
                            <div class="small">{{ $product->date_available->format('M j, Y') }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small mb-1 text-uppercase" style="letter-spacing:.05em;font-size:.75rem">Description</div>
                        <div style="font-size:.9rem;color:#374151">{!! $product->description !!}</div>
                    </div>

                    <div class="pt-3 border-top">
                        <span class="text-muted small">Created by {{ $product->creator->name ?? '—' }}</span>
                    </div>
                </div>

                {{-- Approval status (managers/admin only) --}}
                @if(auth()->check() && auth()->user()->canManageProducts())
                <div class="mt-3 pt-3 border-top">
                    <span class="badge bg-{{ $product->getStatusBadgeClass() }} rounded-pill px-3 py-2 me-2">
                        <i class="fa fa-{{ $product->isApproved() ? 'check' : ($product->isPending() ? 'clock' : 'times') }} me-1"></i>
                        {{ ucfirst($product->status) }}
                    </span>
                    @if($product->isApproved() && $product->approver)
                        <small class="text-muted">Approved by {{ $product->approver->name }}</small>
                    @endif

                    @if($product->isRejected())
                    <div class="alert alert-danger mt-3 rounded-3" style="border-left:4px solid #dc3545;">
                        <h6 class="fw-bold mb-1"><i class="fa fa-exclamation-triangle me-2"></i>Rejected — Action Required</h6>
                        <p class="mb-2">{{ $product->rejection_reason }}</p>
                        @if($product->created_by === auth()->id())
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('products.edit', $product) }}"
                               class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                <i class="fa fa-edit me-1"></i>Edit &amp; Fix
                            </a>
                            <form method="POST" action="{{ route('products.submit', $product) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-warning rounded-pill px-3">
                                    <i class="fa fa-paper-plane me-1"></i>Resubmit for Approval
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($product->isPending())
                    <div class="alert alert-warning mt-3 rounded-3" style="border-left:4px solid #f59e0b;">
                        <i class="fa fa-clock me-2"></i>
                        <strong>Awaiting admin approval.</strong> This product is not yet visible to customers.
                    </div>
                    @endif
                </div>
                @endif

            </div>
        </div>
    </div>

</div>
@endsection
