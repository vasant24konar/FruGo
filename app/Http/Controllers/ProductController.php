<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Mail\ProductStatusMail;
use App\Mail\ProductSubmittedMail;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $products = $this->productService->listProducts(15, $search ?: null);

        return view('products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);
        if ($path = $this->storeImage($request)) {
            $data['image_path'] = $path;
        }
        $product = $this->productService->createProduct($data);

        if ($product->isPending()) {
            $this->notifyAdminsOfSubmission($product);
            return redirect()
                ->route('products.show', $product)
                ->with('success', 'Product submitted for admin approval. You will be notified by email.');
        }

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Product created and published successfully.');
    }

    public function show(Product $product): View
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $this->authorizeModification($product);

        return view('products.edit', compact('product'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorizeModification($product);

        $wasRejected = $product->isRejected();
        $data = $request->validated();
        unset($data['image']);
        if ($path = $this->storeImage($request)) {
            $data['image_path'] = $path;
        }
        $this->productService->updateProduct($product, $data);
        $product->refresh();

        if ($wasRejected && $product->isPending()) {
            $this->notifyAdminsOfSubmission($product);
            return redirect()
                ->route('products.show', $product)
                ->with('success', 'Product updated and resubmitted for approval.');
        }

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeModification($product);

        $this->productService->deleteProduct($product);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function approve(Request $request, Product $product): RedirectResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        abort_unless($product->isPending(), 422, 'Product is not pending approval.');

        $product->update([
            'status'           => Product::STATUS_APPROVED,
            'approved_by'      => Auth::id(),
            'approved_at'      => now(),
            'rejection_reason' => null,
        ]);

        if ($product->creator) {
            Mail::to($product->creator->email)
                ->send(new ProductStatusMail($product, 'approved'));
        }

        return back()->with('success', "Product \"{$product->title}\" approved and is now live.");
    }

    public function reject(Request $request, Product $product): RedirectResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        abort_unless($product->isPending(), 422, 'Product is not pending approval.');

        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:1000',
        ]);

        $product->update([
            'status'           => Product::STATUS_REJECTED,
            'rejection_reason' => $request->rejection_reason,
            'approved_by'      => null,
            'approved_at'      => null,
        ]);

        if ($product->creator) {
            Mail::to($product->creator->email)
                ->send(new ProductStatusMail($product, 'rejected', $request->rejection_reason));
        }

        return back()->with('success', "Product \"{$product->title}\" rejected. Manager has been notified.");
    }

    public function submit(Product $product): RedirectResponse
    {
        $this->authorizeModification($product);
        abort_unless($product->isRejected(), 422, 'Only rejected products can be resubmitted.');

        $product->update([
            'status'           => Product::STATUS_PENDING,
            'rejection_reason' => null,
        ]);

        $this->notifyAdminsOfSubmission($product);

        return back()->with('success', 'Product resubmitted for approval. Admin will review it shortly.');
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image') || ! $request->file('image')->isValid()) {
            return null;
        }
        $path = $request->file('image')->store('products', 'public');
        return 'storage/' . $path;
    }

    private function authorizeModification(Product $product): void
    {
        if (! $this->productService->canModify(Auth::user(), $product)) {
            abort(403, 'You do not have permission to modify this product.');
        }
    }

    private function notifyAdminsOfSubmission(Product $product): void
    {
        $admins = User::where('role', User::ROLE_ADMIN)->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)
                ->send(new ProductSubmittedMail($product, Auth::user()));
        }
    }
}
