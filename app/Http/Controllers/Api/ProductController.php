<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\ProductActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseApiController
{
    /**
     * Retrieve all products.
     */
    public function index()
    {
        return $this->respondWithSuccess(
            Product::latest()->get()
        );
    }

    /**
     * Create a new product.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
        ]);

        $product = Product::create($data);

        ProductActivity::create([
            'product_id' => $product->id,
            'action' => 'created',
            'product_name' => $product->name,
            'product_price' => $product->price,
            'description' => 'Product created successfully.',
        ]);

        return $this->respondCreated($product);
    }

    /**
     * Retrieve a single product.
     */
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->respondNotFound('Product not found');
        }

        return $this->respondWithSuccess($product);
    }

    /**
     * Update an existing product.
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->respondNotFound('Product not found');
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|integer|min:0',
        ]);

        $product->update($data);

        ProductActivity::create([
            'product_id' => $product->id,
            'action' => 'updated',
            'product_name' => $product->name,
            'product_price' => $product->price,
            'description' => 'Product updated successfully.',
        ]);

        return $this->respondWithSuccess($product);
    }

    /**
     * Delete a product.
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->respondNotFound('Product not found');
        }

        ProductActivity::create([
            'product_id' => $product->id,
            'action' => 'deleted',
            'product_name' => $product->name,
            'product_price' => $product->price,
            'description' => 'Product deleted successfully.',
        ]);

        $product->delete();

        return $this->respondOk('Deleted successfully');
    }

    /**
     * Search, filter, sort and paginate products.
     *
     * Example:
     * /api/products/search?search=iphone
     * /api/products/search?min_price=50000&max_price=100000
     * /api/products/search?sort=price&direction=desc
     */
    public function search(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'min_price' => 'nullable|integer|min:0',
            'max_price' => 'nullable|integer|min:0',
            'sort' => 'nullable|in:id,name,price,created_at,updated_at',
            'direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Product::query();

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->filled('min_price')) {
            $query->where(
                'price',
                '>=',
                $request->input('min_price')
            );
        }

        if ($request->filled('max_price')) {
            $query->where(
                'price',
                '<=',
                $request->input('max_price')
            );
        }

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $perPage = $request->input('per_page', 10);

        $products = $query
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->appends($request->query());

        return $this->respondWithSuccess($products);
    }

    /**
     * Product statistics and analytics.
     */
    public function analytics()
    {
        $totalProducts = Product::count();

        $totalValue = Product::sum('price');

        $averagePrice = Product::avg('price');

        $minimumPrice = Product::min('price');

        $maximumPrice = Product::max('price');

        $highestPricedProduct = Product::orderByDesc('price')->first();

        $lowestPricedProduct = Product::orderBy('price')->first();

        $recentProducts = Product::latest()
            ->limit(5)
            ->get();

        $activitySummary = ProductActivity::select(
            'action',
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('action')
            ->orderBy('action')
            ->get();

        $analytics = [
            'summary' => [
                'total_products' => $totalProducts,
                'total_inventory_value' => $totalValue,
                'average_product_price' => round((float) $averagePrice, 2),
                'minimum_product_price' => $minimumPrice,
                'maximum_product_price' => $maximumPrice,
            ],

            'highest_priced_product' => $highestPricedProduct,

            'lowest_priced_product' => $lowestPricedProduct,

            'activity_summary' => $activitySummary,

            'recent_products' => $recentProducts,
        ];

        return $this->respondWithSuccess($analytics);
    }

    /**
     * Product activity history.
     */
    public function history(Request $request)
    {
        $request->validate([
            'action' => 'nullable|in:created,updated,deleted',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = ProductActivity::query()
            ->with('product')
            ->latest();

        if ($request->filled('action')) {
            $query->where(
                'action',
                $request->input('action')
            );
        }

        $activities = $query->paginate(
            $request->input('per_page', 10)
        );

        return $this->respondWithSuccess($activities);
    }

    /**
     * Export product activity history as CSV.
     */
    public function exportHistory(Request $request)
    {
        $request->validate([
            'action' => 'nullable|in:created,updated,deleted',
        ]);

        $query = ProductActivity::query()->latest();

        if ($request->filled('action')) {
            $query->where(
                'action',
                $request->input('action')
            );
        }

        $activities = $query->get();

        $fileName = 'product-activity-history-' . now()->format('Y-m-d-H-i-s') . '.csv';

        return response()->streamDownload(function () use ($activities) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Product ID',
                'Action',
                'Product Name',
                'Product Price',
                'Description',
                'Created At',
            ]);

            foreach ($activities as $activity) {
                fputcsv($handle, [
                    $activity->id,
                    $activity->product_id,
                    $activity->action,
                    $activity->product_name,
                    $activity->product_price,
                    $activity->description,
                    $activity->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}