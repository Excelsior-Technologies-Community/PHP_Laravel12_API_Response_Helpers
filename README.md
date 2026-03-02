#  PHP_Laravel12_API_Response_Helpers

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue)
![Status](https://img.shields.io/badge/Status-Production%20Ready-brightgreen)

---

##  Overview

This project demonstrates how to build a **clean, production‑ready REST API** using **Laravel 12** and the `f9webltd/laravel-api-response-helpers` package.

It standardizes JSON responses, simplifies controller logic, and ensures proper HTTP status codes across your API.

This guide walks you through:

* Creating a fresh Laravel 12 project
* Installing API Response Helpers
* Enabling API routing
* Creating a Base API Controller
* Building a complete CRUD API (Products)
* Testing endpoints using Postman

---

##  Features

*  Standardized JSON responses
*  Clean and reusable controller methods
*  Proper HTTP status codes
*  Production-ready structure
*  Easy Postman testing
*  Simple and scalable architecture

---

##  Requirements

* PHP 8.2+
* Composer
* MySQL / MariaDB
* Laravel 12

---

##  Folder Structure

```
app/
 ├── Http/
 │   └── Controllers/
 │       └── Api/
 │           ├── BaseApiController.php
 │           └── ProductController.php
 ├── Models/
 │   └── Product.php

database/
 └── migrations/

routes/
 └── api.php
```

---

## Step 1 — Create Laravel Project

```bash
composer create-project laravel/laravel laravel-api
```

---

## Step 2 — Environment Setup

Update `.env`:

```
APP_NAME="Laravel API"
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:

```bash
php artisan migrate
```

---

## Step 3 — Install API Response Helpers

```bash
composer require f9webltd/laravel-api-response-helpers
```

No publishing required.

---

## Step 4 — Enable API Routing

Edit `bootstrap/app.php`

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {
        //
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();
```

---

## Step 5 — Create Base API Controller

Create file: `app/Http/Controllers/Api/BaseApiController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use F9Web\ApiResponseHelpers;

class BaseApiController extends Controller
{
    use ApiResponseHelpers;
}
```

---

## Step 6 — Create Product Model & Migration

```bash
php artisan make:model Product -m
```

Migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('price');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

Run:

```bash
php artisan migrate
```

Model:

`app/Models/Product.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price'
    ];
}
```

---

## Step 7 — Product Controller (CRUD)

```bash
php artisan make:controller Api/ProductController --api
```

`app/Http/Controllers/Api/ProductController.php`

```php
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
```

---

## Step 8 — API Routes

`routes/api.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

Route::apiResource('products', ProductController::class);
```

---

## Step 9 — Run Server

```bash
php artisan serve
```

Base URL:

```
http://127.0.0.1:8000/api
```

---

## Step 10 — Postman Testing

### 1. GET ALL PRODUCTS

**Request**

```
GET   http://127.0.0.1:8000/api/products
```

**Headers**

```
Accept: application/json
Content-Type: application/json
```

**Response**

<img width="904" height="806" alt="Screenshot 2026-03-02 130932" src="https://github.com/user-attachments/assets/1d476293-a215-480f-bdc7-c68e4fcab895" />

---

### 2. CREATE PRODUCT (POST)

**Request**

```
POST   http://127.0.0.1:8000/api/products
```

**Body → RAW → JSON**

```json
{
  "name": "iPhone 15",
  "price": 80000
}
```

**Response**

<img width="788" height="655" alt="Screenshot 2026-03-02 122430" src="https://github.com/user-attachments/assets/0c3a1193-4fd0-4b96-92b3-8ec22f278d5a" />

---

### 3. SHOW SINGLE PRODUCT

**Request**

```
GET  http://127.0.0.1:8000/api/products/2
```

**Response**

<img width="867" height="698" alt="image" src="https://github.com/user-attachments/assets/208fe312-c2c5-4417-b8cb-16cb2a405339" />


---

### 4. UPDATE PRODUCT

**Request**

```
PUT  http://127.0.0.1:8000/api/products/1
```

**Body (JSON)**

```json
{
  "name": "iPhone 17",
  "price": 90000
}
```

**Response**

<img width="803" height="666" alt="Screenshot 2026-03-02 123319" src="https://github.com/user-attachments/assets/8b0d5686-f346-4b33-bdf3-bf5af33b043a" />

---

### 5. DELETE PRODUCT

**Request**

```
DELETE  http://127.0.0.1:8000/api/products/1
```

**Response**

<img width="725" height="582" alt="Screenshot 2026-03-02 123416" src="https://github.com/user-attachments/assets/096f1f6b-ab9f-44d3-b196-d5f9ae819cfe" />

---

### 6. VALIDATION ERROR TEST

Send empty body:

```json
{}
```

**Response (Auto Laravel Validation)**

<img width="754" height="699" alt="Screenshot 2026-03-02 123525" src="https://github.com/user-attachments/assets/1bb89d22-b202-4e3a-803f-9f00fff59aaa" />

---

## Troubleshooting

* Ensure `api.php` routing is enabled in `bootstrap/app.php`
* Always send `Accept: application/json`
* Run `php artisan migrate` after creating migrations


