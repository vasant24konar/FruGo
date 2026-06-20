@extends('layouts.shop')
@section('title', 'Add Product')

@section('content')
<div class="container py-5">

    <div class="mb-4">
        <a href="{{ route('products.index') }}" class="text-decoration-none" style="color:var(--primary)">
            <i class="fa fa-arrow-left me-1"></i>Back to Products
        </a>
        <h2 class="fw-bold mt-2" style="font-family:'Raleway',sans-serif">
            <i class="fa fa-plus-circle me-2" style="color:var(--primary)"></i>Add New Product
        </h2>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:.85rem;overflow:hidden">
                <div class="card-header border-0 py-3 px-4" style="background:var(--primary)">
                    <h6 class="mb-0 text-white fw-bold"><i class="fa fa-leaf me-2"></i>Product Details</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                        @csrf
                        @include('products._form')
                        <div class="d-flex justify-content-end gap-2 pt-2 mt-3 border-top">
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn fw-semibold px-4" style="background:var(--primary);color:#fff">
                                <i class="fa fa-save me-1"></i>Create Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
