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
            Product::oldest()->get()
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

    /*
    |--------------------------------------------------------------------------
    | Existing Functionality
    |--------------------------------------------------------------------------
    */

    /**
     * Search, filter, sort and paginate products.
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

            $query->where(
                'name',
                'like',
                '%' . $search . '%'
            );
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
        $perPage = $request->input('per_page', 5);

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

        $recentProducts = Product::oldest()
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
                'average_product_price' => round(
                    (float) $averagePrice,
                    2
                ),
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
            ->oldest();

        if ($request->filled('action')) {
            $query->where(
                'action',
                $request->input('action')
            );
        }

        $activities = $query->paginate(
            $request->input('per_page', 5)
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

        $query = ProductActivity::query()->oldest();

        if ($request->filled('action')) {
            $query->where(
                'action',
                $request->input('action')
            );
        }

        $activities = $query->get();

        $fileName =
            'product-activity-history-' .
            now()->format('Y-m-d-H-i-s') .
            '.csv';

        return response()->streamDownload(
            function () use ($activities) {

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
                        $activity->created_at?->format(
                            'Y-m-d H:i:s'
                        ),
                    ]);
                }

                fclose($handle);
            },
            $fileName,
            [
                'Content-Type' => 'text/csv',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NEW FUNCTIONALITY 1
    |--------------------------------------------------------------------------
    */

    /**
     * Bulk delete products.
     *
     * DELETE /api/products/bulk-delete
     */
    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:products,id',
        ]);

        $products = Product::whereIn('id', $data['ids'])->get();

        foreach ($products as $product) {
            ProductActivity::create([
                'product_id' => $product->id,
                'action' => 'deleted',
                'product_name' => $product->name,
                'product_price' => $product->price,
                'description' => 'Product deleted using bulk delete.',
            ]);
        }

        $deletedCount = Product::whereIn(
            'id',
            $data['ids']
        )->delete();

        return $this->respondWithSuccess([
            'message' => 'Products deleted successfully.',
            'deleted_count' => $deletedCount,
            'deleted_ids' => $data['ids'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NEW FUNCTIONALITY 2
    |--------------------------------------------------------------------------
    */

    /**
     * Bulk update product prices.
     *
     * PUT /api/products/bulk-price
     */
    public function bulkUpdatePrice(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:products,id',
            'price' => 'required|integer|min:0',
        ]);

        $products = Product::whereIn(
            'id',
            $data['ids']
        )->get();

        foreach ($products as $product) {
            $product->update([
                'price' => $data['price'],
            ]);

            ProductActivity::create([
                'product_id' => $product->id,
                'action' => 'updated',
                'product_name' => $product->name,
                'product_price' => $product->price,
                'description' => 'Product price updated using bulk update.',
            ]);
        }

        return $this->respondWithSuccess([
            'message' => 'Product prices updated successfully.',
            'updated_count' => $products->count(),
            'new_price' => $data['price'],
            'product_ids' => $data['ids'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NEW FUNCTIONALITY 3
    |--------------------------------------------------------------------------
    */

    /**
     * Duplicate an existing product.
     *
     * POST /api/products/{id}/duplicate
     */
    public function duplicate($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->respondNotFound('Product not found');
        }

        $duplicate = Product::create([
            'name' => $product->name . ' Copy',
            'price' => $product->price,
        ]);

        ProductActivity::create([
            'product_id' => $duplicate->id,
            'action' => 'created',
            'product_name' => $duplicate->name,
            'product_price' => $duplicate->price,
            'description' => 'Product duplicated from product #' . $product->id,
        ]);

        return $this->respondCreated($duplicate);
    }

    /*
    |--------------------------------------------------------------------------
    | NEW FUNCTIONALITY 4
    |--------------------------------------------------------------------------
    */

    /**
     * Export products as CSV.
     *
     * GET /api/products/export
     */
    public function exportProducts(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'min_price' => 'nullable|integer|min:0',
            'max_price' => 'nullable|integer|min:0',
        ]);

        $query = Product::query()->oldest();

        if ($request->filled('search')) {
            $query->where(
                'name',
                'like',
                '%' . $request->input('search') . '%'
            );
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

        $products = $query->get();

        $fileName =
            'products-' .
            now()->format('Y-m-d-H-i-s') .
            '.csv';

        return response()->streamDownload(
            function () use ($products) {

                $handle = fopen('php://output', 'w');

                fputcsv($handle, [
                    'ID',
                    'Name',
                    'Price',
                    'Created At',
                    'Updated At',
                ]);

                foreach ($products as $product) {
                    fputcsv($handle, [
                        $product->id,
                        $product->name,
                        $product->price,
                        $product->created_at?->format(
                            'Y-m-d H:i:s'
                        ),
                        $product->updated_at?->format(
                            'Y-m-d H:i:s'
                        ),
                    ]);
                }

                fclose($handle);
            },
            $fileName,
            [
                'Content-Type' => 'text/csv',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NEW FUNCTIONALITY 5
    |--------------------------------------------------------------------------
    */

    /**
     * Product price range summary.
     *
     * GET /api/products/price-summary
     */
    public function priceSummary()
    {
        $summary = [
            'free' => Product::where('price', 0)->count(),

            'under_1000' => Product::whereBetween(
                'price',
                [1, 999]
            )->count(),

            '1000_to_9999' => Product::whereBetween(
                'price',
                [1000, 9999]
            )->count(),

            '10000_to_49999' => Product::whereBetween(
                'price',
                [10000, 49999]
            )->count(),

            '50000_and_above' => Product::where(
                'price',
                '>=',
                50000
            )->count(),
        ];

        return $this->respondWithSuccess([
            'price_ranges' => $summary,
            'total_products' => array_sum($summary),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NEW FUNCTIONALITY 6
    |--------------------------------------------------------------------------
    */

    /**
     * Get top expensive products.
     *
     * GET /api/products/top-expensive
     */
    public function topExpensive(Request $request)
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $limit = $request->input('limit', 5);

        $products = Product::orderByDesc('price')
            ->limit($limit)
            ->get();

        return $this->respondWithSuccess([
            'limit' => $limit,
            'products' => $products,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NEW FUNCTIONALITY 7
    |--------------------------------------------------------------------------
    */

    /**
     * Get recent products.
     *
     * GET /api/products/recent
     */
    public function recentProducts(Request $request)
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $limit = $request->input('limit', 5);

        $products = Product::oldest()
            ->limit($limit)
            ->get();

        return $this->respondWithSuccess([
            'limit' => $limit,
            'products' => $products,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NEW FUNCTIONALITY 8
    |--------------------------------------------------------------------------
    */

    /**
     * Product name suggestions.
     *
     * GET /api/products/suggestions?q=phone
     */
    public function suggestions(Request $request)
    {
        $data = $request->validate([
            'q' => 'required|string|min:1|max:100',
        ]);

        $products = Product::where(
            'name',
            'like',
            '%' . $data['q'] . '%'
        )
            ->orderBy('name')
            ->limit(5)
            ->get([
                'id',
                'name',
                'price',
            ]);

        return $this->respondWithSuccess([
            'query' => $data['q'],
            'count' => $products->count(),
            'suggestions' => $products,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NEW FUNCTIONALITY 9
    |--------------------------------------------------------------------------
    */

    /**
     * Compare multiple products.
     *
     * GET /api/products/compare?ids[]=1&ids[]=2&ids[]=3
     */
    public function compare(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:2|max:10',
            'ids.*' => 'integer|exists:products,id',
        ]);

        $products = Product::whereIn(
            'id',
            $data['ids']
        )
            ->orderBy('price')
            ->get();

        return $this->respondWithSuccess([
            'requested_ids' => $data['ids'],
            'count' => $products->count(),
            'products' => $products,
        ]);
    }
}