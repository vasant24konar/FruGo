@extends('layouts.shop')
@section('title', 'Manager Dashboard')

@section('content')
<div class="container py-5">

    {{-- Page header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family:'Raleway',sans-serif">
                <i class="fa fa-chart-line me-2" style="color:var(--bs-primary)"></i>Manager Dashboard
            </h2>
            <p class="text-muted small mb-0">Welcome back, {{ auth()->user()->name }} &mdash; {{ ucfirst(str_replace('_',' ', auth()->user()->role)) }}</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn fw-semibold" style="background:var(--bs-primary);color:#fff;border-radius:.6rem">
            <i class="fa fa-plus me-1"></i>Add Product
        </a>
    </div>

    {{-- Stats row --}}
    <div class="row g-3 mb-5">
        @foreach([
            ['fa-box-open',   'Total Products', $stats['total_products'], '#86B817', '#f0f9e8'],
            ['fa-shopping-bag','Total Orders',  $stats['total_orders'],   '#3b82f6', '#eff6ff'],
            ['fa-dollar-sign','Revenue',        '$'.number_format($stats['total_revenue'],2), '#10b981', '#ecfdf5'],
            ['fa-users',      'Customers',      $stats['total_customers'], '#f59e0b', '#fffbeb'],
        ] as [$icon, $label, $value, $color, $bg])
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:.85rem">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:{{ $bg }}">
                        <i class="fa {{ $icon }} fa-lg" style="color:{{ $color }}"></i>
                    </div>
                    <div>
                        <div class="text-muted small">{{ $label }}</div>
                        <div class="fw-bold fs-5" style="color:{{ $color }}">{{ $value }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-4">

        {{-- Pending Orders to Dispatch --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:.85rem">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
                    <i class="fa fa-truck" style="color:var(--bs-primary)"></i>
                    <h6 class="mb-0 fw-bold" style="font-family:'Raleway',sans-serif">Orders to Dispatch</h6>
                    <span class="badge ms-auto" style="background:var(--bs-primary)">{{ $pendingOrders->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($pendingOrders->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa fa-check-circle fa-2x mb-2" style="color:var(--bs-primary)"></i>
                            <p class="mb-0">All orders dispatched!</p>
                        </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle" style="font-size:.875rem">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Order</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($pendingOrders as $order)
                            <tr>
                                <td class="ps-4 fw-semibold">#{{ $order->id }}</td>
                                <td>{{ $order->shipping_name ?? ($order->user->name ?? '—') }}</td>
                                <td>{{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}</td>
                                <td class="fw-semibold">${{ number_format($order->total, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->getStatusBadgeClass() }}">{{ ucfirst($order->status) }}</span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('orders.updateStatus', $order) }}" class="d-flex gap-1">
                                        @csrf
                                        <select name="status" class="form-select form-select-sm" style="width:130px;font-size:.78rem">
                                            <option value="pending"    {{ $order->status==='pending'    ? 'selected' : '' }}>Pending</option>
                                            <option value="processing" {{ $order->status==='processing' ? 'selected' : '' }}>Processing</option>
                                            <option value="completed"  {{ $order->status==='completed'  ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled"  {{ $order->status==='cancelled'  ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                        <button class="btn btn-sm fw-semibold" style="background:var(--bs-primary);color:#fff;white-space:nowrap">
                                            Update
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Stock Alerts --}}
        <div class="col-lg-4">

            {{-- Out of Stock --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:.85rem">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
                    <i class="fa fa-times-circle text-danger"></i>
                    <h6 class="mb-0 fw-bold" style="font-family:'Raleway',sans-serif">Out of Stock</h6>
                    <span class="badge bg-danger ms-auto">{{ $outOfStock->count() }}</span>
                </div>
                <div class="card-body px-4 pb-4 pt-2">
                    @if($outOfStock->isEmpty())
                        <p class="text-muted small mb-0">No out-of-stock products.</p>
                    @else
                    <ul class="list-unstyled mb-0">
                        @foreach($outOfStock as $p)
                        <li class="d-flex align-items-center justify-content-between py-2 border-bottom" style="font-size:.85rem">
                            <span class="fw-semibold text-truncate me-2" style="max-width:160px">{{ $p->title }}</span>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <span class="badge bg-danger">0 left</span>
                                <a href="{{ route('products.edit', $p) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:.75rem">Edit</a>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>

            {{-- Low Stock --}}
            <div class="card border-0 shadow-sm" style="border-radius:.85rem">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
                    <i class="fa fa-exclamation-triangle text-warning"></i>
                    <h6 class="mb-0 fw-bold" style="font-family:'Raleway',sans-serif">Low Stock (&lt;10)</h6>
                    <span class="badge bg-warning text-dark ms-auto">{{ $lowStock->count() }}</span>
                </div>
                <div class="card-body px-4 pb-4 pt-2">
                    @if($lowStock->isEmpty())
                        <p class="text-muted small mb-0">All products well stocked.</p>
                    @else
                    <ul class="list-unstyled mb-0">
                        @foreach($lowStock as $p)
                        <li class="d-flex align-items-center justify-content-between py-2 border-bottom" style="font-size:.85rem">
                            <span class="fw-semibold text-truncate me-2" style="max-width:150px">{{ $p->title }}</span>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <span class="badge bg-warning text-dark">{{ $p->stock }} left</span>
                                <a href="{{ route('products.edit', $p) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:.75rem">Edit</a>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- Pending Product Approvals (admin only) --}}
    @if(auth()->user()->isAdmin() && $pendingProducts->count() > 0)
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius:.85rem">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
                    <i class="fa fa-clock text-warning"></i>
                    <h6 class="mb-0 fw-bold" style="font-family:'Raleway',sans-serif">Products Awaiting Approval</h6>
                    <span class="badge bg-warning text-dark ms-auto">{{ $pendingProducts->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle" style="font-size:.875rem">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Submitted By</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($pendingProducts as $p)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $p->title }}</td>
                                <td><span class="badge bg-secondary rounded-pill">{{ ucfirst($p->category ?? 'General') }}</span></td>
                                <td class="fw-semibold text-primary">${{ number_format($p->price, 2) }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa fa-user-circle text-muted"></i>
                                        <div>
                                            <div>{{ $p->creator?->name ?? '—' }}</div>
                                            <small class="text-muted">{{ $p->creator?->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $p->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('products.show', $p) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Preview">
                                            <i class="fa fa-eye me-1"></i>View
                                        </a>
                                        <form method="POST" action="{{ route('products.approve', $p) }}">
                                            @csrf
                                            <button class="btn btn-sm py-1 px-2" style="background:#86B817;color:#fff;" title="Approve">
                                                <i class="fa fa-check me-1"></i>Approve
                                            </button>
                                        </form>
                                        <button class="btn btn-sm btn-danger py-1 px-2"
                                                data-bs-toggle="modal" data-bs-target="#rejectModal{{ $p->id }}" title="Reject">
                                            <i class="fa fa-times me-1"></i>Reject
                                        </button>
                                    </div>

                                    {{-- Reject Modal --}}
                                    <div class="modal fade" id="rejectModal{{ $p->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-3">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold">Reject: {{ Str::limit($p->title, 30) }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="{{ route('products.reject', $p) }}">
                                                    @csrf
                                                    <div class="modal-body pt-2">
                                                        <label class="form-label fw-semibold">Reason for Rejection <sup class="text-danger">*</sup></label>
                                                        <textarea name="rejection_reason" rows="4" required minlength="10"
                                                                  class="form-control"
                                                                  placeholder="Explain what needs to be changed — this message will be emailed to the product manager."></textarea>
                                                        <small class="text-muted mt-1 d-block">
                                                            <i class="fa fa-info-circle me-1"></i>The manager will receive this feedback by email and can update &amp; resubmit.
                                                        </small>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger rounded-pill px-4">
                                                            <i class="fa fa-times me-1"></i>Reject &amp; Notify
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @elseif(auth()->user()->isAdmin())
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius:.85rem">
                <div class="card-body py-4 text-center text-muted">
                    <i class="fa fa-check-circle fa-2x mb-2" style="color:#86B817;"></i>
                    <p class="mb-0 fw-semibold">No products pending approval</p>
                    <small>All submissions have been reviewed.</small>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
