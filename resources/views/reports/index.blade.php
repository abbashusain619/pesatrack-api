@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Spending Reports</h1>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow mb-6">
        <form id="report-filters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="date" name="start_date" id="start_date" class="border rounded px-3 py-2" placeholder="Start date">
            <input type="date" name="end_date" id="end_date" class="border rounded px-3 py-2" placeholder="End date">
            <select name="account_id" id="account_id" class="border rounded px-3 py-2">
                <option value="">All Accounts</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
            <select name="category_id" id="category_id" class="border rounded px-3 py-2">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Apply Filters</button>
                <button type="reset" id="reset-filters" class="bg-gray-300 text-gray-800 px-4 py-2 rounded">Reset</button>
            </div>
            <a href="{{ route('reports.export', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded">Export CSV</a>
        </form>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Spending by Category</h2>
            <canvas id="categoryPieChart" width="400" height="400"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Income vs Expense Over Time</h2>
            <canvas id="incomeExpenseChart" width="400" height="400"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow mt-6">
            <h2 class="text-xl font-semibold mb-4">Net Worth Trend (monthly)</h2>
            <canvas id="networthChart" width="400" height="200"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    let pieChart, barChart, lineChart;

    async function loadReports() {
        // 1️⃣ Load spending pie & income/expense bar (existing)
        const params = new URLSearchParams({
            start_date: document.getElementById('start_date').value,
            end_date: document.getElementById('end_date').value,
            account_id: document.getElementById('account_id').value,
            category_id: document.getElementById('category_id').value,
        });

        const response = await fetch(`{{ route('reports.data') }}?${params}`);
        const data = await response.json();

        // Pie chart (spending by category)
        const pieCtx = document.getElementById('categoryPieChart').getContext('2d');
        if (pieChart) pieChart.destroy();
        pieChart = new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: data.spending_by_category.map(item => item.category),
                datasets: [{
                    data: data.spending_by_category.map(item => item.total),
                    backgroundColor: ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#14B8A6', '#6B7280', '#F97316', '#06B6D4'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw.toFixed(2)}` } }
                }
            }
        });

        // Bar chart (income vs expense over time)
        const barCtx = document.getElementById('incomeExpenseChart').getContext('2d');
        if (barChart) barChart.destroy();
        barChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: data.income_expense.labels,
                datasets: [
                    { label: 'Income', data: data.income_expense.income, backgroundColor: '#10B981' },
                    { label: 'Expenses', data: data.income_expense.expenses, backgroundColor: '#EF4444' }
                ]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Amount' } } }
            }
        });

        // 2️⃣ Net Worth Trend (line chart) – uses only date range from filters
        const nwParams = new URLSearchParams({
            start_date: document.getElementById('start_date').value,
            end_date: document.getElementById('end_date').value,
        });
        const nwResponse = await fetch(`{{ route('reports.networth') }}?${nwParams}`);
        const nwData = await nwResponse.json();

        const nwCtx = document.getElementById('networthChart').getContext('2d');
        if (lineChart) lineChart.destroy();
        lineChart = new Chart(nwCtx, {
            type: 'line',
            data: {
                labels: nwData.labels,
                datasets: [{
                    label: 'Net Worth',
                    data: nwData.data,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.1,
                    pointRadius: 3,
                    pointBackgroundColor: '#3B82F6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: false,
                        title: { display: true, text: 'Amount (' + '{{ auth()->user()->base_currency ?? 'USD' }}' + ')' }
                    },
                    x: {
                        title: { display: true, text: 'Month End' }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `Net Worth: ${ctx.raw.toFixed(2)}`
                        }
                    }
                }
            }
        });
    }

    // Event listeners for filters
    document.getElementById('report-filters').addEventListener('submit', function(e) {
        e.preventDefault();
        loadReports();
    });
    document.getElementById('reset-filters').addEventListener('click', function() {
        document.getElementById('start_date').value = '';
        document.getElementById('end_date').value = '';
        document.getElementById('account_id').value = '';
        document.getElementById('category_id').value = '';
        loadReports();
    });

    // Initial load
    loadReports();
</script>
@endsection