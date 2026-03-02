<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    // Retrieve and return all products
    public function index()
    {
        return $this->respondWithSuccess(Product::all());
    }

    // Validate input and create a new product
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'price' => 'required|integer'
        ]);

        $product = Product::create($data);

        return $this->respondCreated($product);
    }

    // Find and return a single product by ID
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->respondNotFound('Product not found');
        }

        return $this->respondWithSuccess($product);
    }

    // Validate input and update an existing product
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->respondNotFound();
        }

        $data = $request->validate([
            'name' => 'sometimes|string',
            'price' => 'sometimes|integer'
        ]);

        $product->update($data);

        return $this->respondWithSuccess($product);
    }

    // Delete a product by ID
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->respondNotFound();
        }

        $product->delete();

        return $this->respondOk('Deleted successfully');
    }
}