@extends('layouts.shop')
@section('title', 'Manage Products')

@section('content')
<div class="container py-5">

    <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family:'Raleway',sans-serif">
                <i class="fa fa-box-open me-2" style="color:var(--bs-primary)"></i>Products
            </h2>
            <p class="text-muted small mb-0">{{ $products->total() }} product{{ $products->total() !== 1 ? 's' : '' }} total</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fa fa-tachometer-alt me-1"></i>Dashboard
            </a>
            <a href="{{ route('products.create') }}"
               class="btn fw-semibold rounded-pill px-4 py-2"
               style="background:#86B817;color:#fff;font-size:.95rem;">
                <i class="fa fa-plus me-2"></i>Add Product
            </a>
        </div>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('products.index') }}" class="mb-4">
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <div class="input-group rounded-pill overflow-hidden border" style="max-width:420px;border-color:#86B817 !important;">
                <span class="input-group-text bg-white border-0"><i class="fa fa-search" style="color:#86B817"></i></span>
                <input type="text" name="search" value="{{ $search }}"
                       class="form-control border-0" placeholder="Search by title…">
                <button type="submit" class="btn px-4 fw-semibold border-0" style="background:#86B817;color:#fff;">Search</button>
            </div>
            @if($search)
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fa fa-times me-1"></i>Clear
                </a>
            @endif
        </div>
    </form>

    @if($products->isEmpty())
        <div class="text-center py-5">
            <i class="fa fa-box-open fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">{{ $search ? 'No products match your search.' : 'No products yet.' }}</h5>
            @if(!$search)
                <a href="{{ route('products.create') }}" class="btn mt-2 fw-semibold" style="background:var(--bs-primary);color:#fff">Add First Product</a>
            @endif
        </div>
    @else
    <div class="card border-0 shadow-sm" style="border-radius:.85rem;overflow:hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f9fafb">
                    <tr>
                        <th class="ps-4 py-3" style="font-size:.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Product</th>
                        <th class="py-3" style="font-size:.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Category</th>
                        <th class="py-3" style="font-size:.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Price</th>
                        <th class="py-3" style="font-size:.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Stock</th>
                        <th class="py-3" style="font-size:.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Available</th>
                        <th class="py-3" style="font-size:.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">By</th>
                        <th class="py-3" style="font-size:.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Status</th>
                        <th class="py-3 pe-4 text-end" style="font-size:.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($products as $product)
                <tr>
                    <td class="ps-4 py-3">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $product->image_path ? asset($product->image_path) : asset('img/fruite-item-1.jpg') }}"
                                 style="width:44px;height:44px;object-fit:cover;border-radius:.5rem;border:1px solid #e5e7eb"
                                 alt="{{ $product->title }}">
                            <div>
                                <div class="fw-semibold" style="font-size:.9rem">{{ $product->title }}</div>
                                <div class="text-muted" style="font-size:.75rem">{{ Str::limit(strip_tags($product->description), 40) }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($product->category)
                            <span class="badge" style="background:#f0f9e8;color:#4a7c15;font-weight:600;font-size:.75rem">{{ ucfirst($product->category) }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="fw-semibold" style="color:var(--bs-primary)">${{ number_format((float)$product->price, 2) }}</td>
                    <td>
                        @if($product->stock == 0)
                            <span class="badge bg-danger">Out of stock</span>
                        @elseif($product->stock < 10)
                            <span class="badge bg-warning text-dark">{{ $product->stock }} left</span>
                        @else
                            <span class="text-muted small">{{ $product->stock }}</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $product->date_available->format('M j, Y') }}</td>
                    <td class="text-muted small">{{ $product->creator->name ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $product->getStatusBadgeClass() }} rounded-pill">
                            {{ ucfirst($product->status) }}
                        </span>
                        @if($product->isRejected())
                            <br><small class="text-danger" title="{{ $product->rejection_reason }}">
                                <i class="fa fa-exclamation-circle me-1"></i>Needs revision
                            </small>
                        @endif
                    </td>
                    <td class="pe-4 text-end">
                        <div class="d-flex gap-1 justify-content-end flex-wrap">
                            <a href="{{ route('products.show', $product) }}"
                               class="btn btn-sm btn-outline-secondary" style="font-size:.78rem">View</a>
                            @if(auth()->user()->isAdmin() || $product->created_by === auth()->id())
                            <a href="{{ route('products.edit', $product) }}"
                               class="btn btn-sm" style="background:var(--bs-primary);color:#fff;font-size:.78rem">Edit</a>
                            @if($product->isRejected() && $product->created_by === auth()->id())
                            <form method="POST" action="{{ route('products.submit', $product) }}">
                                @csrf
                                <button class="btn btn-sm btn-warning" style="font-size:.78rem" title="Resubmit for approval">Resubmit</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('products.destroy', $product) }}"
                                  class="d-inline" onsubmit="return confirm('Delete {{ addslashes($product->title) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:.78rem">Del</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
    @endif

</div>
@endsection
