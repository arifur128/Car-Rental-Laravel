@extends('layouts.myapp')

@section('content')
<div class="flex flex-col flex-1 w-full">
    <main class="h-full overflow-y-auto">
        <div class="container px-6 mx-auto grid mb-12">
            
            <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
                📊 Payment Reports
            </h2>

            {{-- Filter --}}
            <form method="get" class="flex flex-wrap gap-4 items-end bg-white dark:bg-gray-800 p-4 rounded-lg shadow mb-6">
                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300">From</label>
                    <input type="date" name="from" value="{{ $from ?? '' }}" 
                           class="mt-1 p-2 rounded border w-48 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300">To</label>
                    <input type="date" name="to" value="{{ $to ?? '' }}" 
                           class="mt-1 p-2 rounded border w-48 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" 
                            class="mt-1 p-2 rounded border w-48 dark:bg-gray-700 dark:text-white">
                        <option value="">All</option>
                        @foreach(['pending','success','failed','refunded'] as $s)
                            <option value="{{ $s }}" {{ (isset($status) && $status === $s) ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button class="px-4 py-2 bg-pr-400 hover:bg-pr-500 text-white rounded-lg">Filter</button>
                    <a href="{{ route('admin.reports.payments.export', [
                                'from'=>$from ?? now()->subDays(30)->toDateString(),
                                'to'=>$to ?? now()->toDateString()
                             ]) }}"
                       class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg">Export CSV</a>
                </div>
            </form>

            {{-- KPI Cards --}}
            <div class="grid gap-6 mb-6 md:grid-cols-2 xl:grid-cols-4">
                <div class="flex items-center p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                    <div class="p-3 mr-4 bg-pr-400 rounded-full text-white">📦</div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Payments</p>
                        <p class="text-xl font-semibold text-gray-700 dark:text-gray-200">{{ $totalCount ?? 0 }}</p>
                    </div>
                </div>
                <div class="flex items-center p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                    <div class="p-3 mr-4 bg-green-500 rounded-full text-white">✅</div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Success</p>
                        <p class="text-xl font-semibold text-green-600">{{ $successCount ?? 0 }}</p>
                    </div>
                </div>
                <div class="flex items-center p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                    <div class="p-3 mr-4 bg-red-500 rounded-full text-white">❌</div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Failed</p>
                        <p class="text-xl font-semibold text-red-600">{{ $failedCount ?? 0 }}</p>
                    </div>
                </div>
                <div class="flex items-center p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                    <div class="p-3 mr-4 bg-indigo-500 rounded-full text-white">💰</div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Revenue</p>
                        <p class="text-xl font-semibold text-indigo-600">
                            ৳ {{ number_format($sumAmount ?? 0,2) }}
                        </p>
                        <p class="text-xs text-gray-500">Success rate: {{ $successRate ?? 0 }}%</p>
                    </div>
                </div>
            </div>

            {{-- Charts --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10 mb-10">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
                    <h3 class="text-lg font-semibold mb-4">💰 Daily Revenue</h3>
                    <canvas id="revenueChart"></canvas>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
                    <h3 class="text-lg font-semibold mb-4">📊 Success vs Failed</h3>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            {{-- Payments Table --}}
            <div class="w-full overflow-hidden rounded-lg shadow">
                <div class="w-full overflow-x-auto">
                    <table class="w-full whitespace-no-wrap">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b 
                                       dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3">Amount</th>
                                <th class="px-4 py-3">Currency</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                            @forelse($payments ?? [] as $p)
                                <tr class="text-gray-700 dark:text-gray-400">
                                    <td class="px-4 py-3 text-sm">{{ $p->id }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $p->user->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm">৳ {{ number_format($p->amount,2) }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $p->currency }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($p->status == 'success')
                                            <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">
                                                Success
                                            </span>
                                        @elseif($p->status == 'failed')
                                            <span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full">
                                                Failed
                                            </span>
                                        @else
                                            <span class="px-2 py-1 font-semibold leading-tight text-yellow-700 bg-yellow-100 rounded-full">
                                                {{ ucfirst($p->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ $p->created_at->format('d M Y, H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-center text-gray-500">No data found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const revenueLabels = @json($dailyLabels ?? []);
    const revenueData   = @json($dailyRevenue ?? []);
    const successCount  = {{ $successCount ?? 0 }};
    const failedCount   = {{ $failedCount ?? 0 }};

    // Line Chart
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Revenue (৳)',
                data: revenueData,
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76,175,80,0.2)',
                fill: true,
                tension: 0.3
            }]
        }
    });

    // Doughnut Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Success', 'Failed'],
            datasets: [{
                data: [successCount, failedCount],
                backgroundColor: ['#4CAF50', '#F44336']
            }]
        }
    });
</script>
@endpush
