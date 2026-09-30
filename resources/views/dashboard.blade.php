<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Response Helpers & Interactive Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            color: white;
            padding: 30px;
            border-radius: 18px;
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            background: white;
            box-shadow: 0 4px 18px rgba(0,0,0,0.06);
            padding: 22px;
            height: 100%;
        }
        .stat-value {
            font-size: 28px;
            font-weight: 700;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.07);
        }
        #responseViewer {
            background: #1e1e1e;
            color: #4ec9b0;
            font-family: 'Courier New', Courier, monospace;
            padding: 18px;
            border-radius: 10px;
            max-height: 450px;
            overflow-y: auto;
            white-space: pre-wrap;
            font-size: 13px;
        }
        .format-btn.active {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.4);
        }
    </style>
</head>
<body>

<div class="container-fluid py-4 px-4">

    {{-- Header --}}
    <div class="header mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h1 class="mb-1">⚡ API Response Helpers & Live Studio</h1>
                <p class="mb-0 opacity-75">Interactive API Playground, Multi-Format Converter (JSON/XML/CSV) & Batch Processing.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="#testerStudio" class="btn btn-light fw-bold">🚀 Live API Tester</a>
                <a href="#productsSection" class="btn btn-warning fw-bold">📦 Manage Products</a>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="text-muted small">TOTAL PRODUCTS</div>
                <div class="stat-value text-primary">{{ $totalProducts }}</div>
                <small class="text-secondary">Saved in database</small>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="text-muted small">INVENTORY VALUE</div>
                <div class="stat-value text-success">₹{{ number_format($totalValue) }}</div>
                <small class="text-secondary">Combined total value</small>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="text-muted small">AVERAGE PRICE</div>
                <div class="stat-value text-warning">₹{{ number_format($avgPrice, 2) }}</div>
                <small class="text-secondary">Average price per item</small>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="text-muted small">API ACTIVITIES LOGGED</div>
                <div class="stat-value text-info">{{ $totalActivities }}</div>
                <small class="text-secondary">Logged API CRUD actions</small>
            </div>
        </div>
    </div>

    {{-- API Tester Studio & Response Inspector --}}
    <div class="row g-4 mb-4" id="testerStudio">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">⚡ Interactive API Request Studio</h5>
                        <span class="badge bg-primary fs-6">Laravel 12 API</span>
                    </div>

                    <form id="apiTestForm" onsubmit="executeApiCall(event)">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select API Endpoint</label>
                            <select id="endpointSelect" class="form-select" onchange="loadPresetPayload()">
                                <option value="GET|/api/products">GET /api/products - List All Products</option>
                                <option value="POST|/api/products">POST /api/products - Create Product</option>
                                <option value="GET|/api/products/search?q=phone">GET /api/products/search - Search Products</option>
                                <option value="GET|/api/products/analytics">GET /api/products/analytics - Product Analytics</option>
                                <option value="GET|/api/products/price-summary">GET /api/products/price-summary - Price Summary</option>
                                <option value="GET|/api/products/top-expensive">GET /api/products/top-expensive - Top Expensive</option>
                                <option value="GET|/api/products/recent">GET /api/products/recent - Recent Products</option>
                                <option value="GET|/api/products/suggestions?q=smart">GET /api/products/suggestions - Auto Suggestions</option>
                                <option value="POST|/api/products/batch">POST /api/products/batch - Batch Execution Studio</option>
                                <option value="PUT|/api/products/bulk-price">PUT /api/products/bulk-price - Bulk Price Update</option>
                                <option value="GET|/api/products/history">GET /api/products/history - Activity History</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Response Format Switcher</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="formatRadio" id="fmtJson" value="json" checked onchange="updateFormat('json')">
                                <label class="btn btn-outline-primary" for="fmtJson">📄 JSON</label>

                                <input type="radio" class="btn-check" name="formatRadio" id="fmtXml" value="xml" onchange="updateFormat('xml')">
                                <label class="btn btn-outline-primary" for="fmtXml">🏷️ XML</label>

                                <input type="radio" class="btn-check" name="formatRadio" id="fmtCsv" value="csv" onchange="updateFormat('csv')">
                                <label class="btn btn-outline-primary" for="fmtCsv">📊 CSV</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Request Payload (JSON Body)</label>
                            <textarea id="requestBody" class="form-control font-monospace" rows="6" placeholder="Enter JSON payload..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold fs-6">🚀 Send API Request</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Live Response Inspector --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">🔎 Response & Header Inspector</h5>
                        <div id="statusBadgeContainer">
                            <span class="badge bg-secondary fs-6">Ready</span>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-3 text-muted small">
                        <div>⏱️ Execution Time: <strong id="execTime" class="text-dark">0 ms</strong></div>
                        <div>💾 Memory Usage: <strong id="memUsage" class="text-dark">0 KB</strong></div>
                    </div>

                    <div id="responseViewer" class="flex-grow-1">Select an endpoint and click "Send API Request" to view live response...</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Products & Web CRUD Section --}}
    <div class="row g-4" id="productsSection">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">📦 Products Inventory</h5>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createProductModal">➕ Quick Add Product</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Product Name</th>
                                    <th>Price (₹)</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProducts as $prod)
                                    <tr>
                                        <td>#{{ $prod->id }}</td>
                                        <td><strong>{{ $prod->name }}</strong></td>
                                        <td><span class="badge bg-success fs-6">₹{{ number_format($prod->price) }}</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="testProductShow({{ $prod->id }})">👁️ Test GET</button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="testProductDelete({{ $prod->id }})">🗑️ Delete</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Activity History --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">📜 Recent API Activity Logs</h5>
                    <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Action</th>
                                    <th>Product</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivities as $act)
                                    <tr>
                                        <td>
                                            @if($act->action === 'created')
                                                <span class="badge bg-success">Created</span>
                                            @elseif($act->action === 'updated')
                                                <span class="badge bg-warning text-dark">Updated</span>
                                            @else
                                                <span class="badge bg-danger">Deleted</span>
                                            @endif
                                        </td>
                                        <td><small>{{ $act->product_name }}</small></td>
                                        <td><small class="fw-bold">₹{{ number_format($act->product_price) }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">➕ Quick Create Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form onsubmit="createProductWeb(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" id="webProdName" class="form-control" required placeholder="e.g. Smart Watch Pro">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (INR)</label>
                        <input type="number" id="webProdPrice" class="form-control" required placeholder="e.g. 2999">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let selectedFormat = 'json';

    const presets = {
        'POST|/api/products': JSON.stringify({ name: "Pro Gaming Mouse", price: 1499 }, null, 2),
        'POST|/api/products/batch': JSON.stringify({
            operations: [
                { action: "create", data: { name: "Wireless Earbuds", price: 1999 } },
                { action: "get", id: 1 },
                { action: "update", id: 1, data: { price: 1599 } }
            ]
        }, null, 2),
        'PUT|/api/products/bulk-price': JSON.stringify({ percentage: 10, type: "increase" }, null, 2)
    };

    function loadPresetPayload() {
        const val = document.getElementById('endpointSelect').value;
        const reqBody = document.getElementById('requestBody');
        reqBody.value = presets[val] || '';
    }

    function updateFormat(fmt) {
        selectedFormat = fmt;
    }

    async function executeApiCall(e) {
        if (e) e.preventDefault();
        const selectVal = document.getElementById('endpointSelect').value;
        const [method, path] = selectVal.split('|');
        const bodyText = document.getElementById('requestBody').value;
        const viewer = document.getElementById('responseViewer');
        const badgeContainer = document.getElementById('statusBadgeContainer');

        viewer.innerText = "⏳ Requesting API...";

        let url = path;
        if (url.includes('?')) {
            url += `&format=${selectedFormat}`;
        } else {
            url += `?format=${selectedFormat}`;
        }

        const options = {
            method: method,
            headers: {
                'Accept': selectedFormat === 'xml' ? 'application/xml' : (selectedFormat === 'csv' ? 'text/csv' : 'application/json')
            }
        };

        if (method !== 'GET' && bodyText.trim() !== '') {
            options.headers['Content-Type'] = 'application/json';
            options.body = bodyText;
        }

        const startTime = performance.now();
        try {
            const res = await fetch(url, options);
            const endTime = performance.now();
            const duration = (endTime - startTime).toFixed(2);
            document.getElementById('execTime').innerText = duration + ' ms';
            document.getElementById('memUsage').innerText = res.headers.get('X-Memory-Usage-Kb') || 'N/A';

            const statusClass = res.status >= 200 && res.status < 300 ? 'bg-success' : 'bg-danger';
            badgeContainer.innerHTML = `<span class="badge ${statusClass} fs-6">${res.status} ${res.statusText}</span>`;

            const text = await res.text();
            if (selectedFormat === 'json') {
                try {
                    const parsed = JSON.parse(text);
                    viewer.innerText = JSON.stringify(parsed, null, 2);
                } catch {
                    viewer.innerText = text;
                }
            } else {
                viewer.innerText = text;
            }
        } catch (err) {
            badgeContainer.innerHTML = `<span class="badge bg-danger fs-6">Error</span>`;
            viewer.innerText = "❌ Request Failed: " + err.message;
        }
    }

    async function createProductWeb(e) {
        e.preventDefault();
        const name = document.getElementById('webProdName').value;
        const price = document.getElementById('webProdPrice').value;

        const res = await fetch('/api/products', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, price })
        });

        if (res.ok) {
            location.reload();
        } else {
            alert('Error creating product.');
        }
    }

    async function testProductDelete(id) {
        if (!confirm('Delete product #' + id + '?')) return;
        const res = await fetch('/api/products/' + id, { method: 'DELETE' });
        if (res.ok) {
            location.reload();
        }
    }

    function testProductShow(id) {
        document.getElementById('endpointSelect').value = 'GET|/api/products';
        document.getElementById('endpointSelect').value = 'GET|/api/products/' + id;
        executeApiCall();
    }
</script>
</body>
</html>
