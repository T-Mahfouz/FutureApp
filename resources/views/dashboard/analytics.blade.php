@extends('layouts.grain')

@section('title', 'Analytics')

@section('content')

@include('components.notification')

<div class="mb-3 mb-md-4 d-flex justify-content-between">
    <div class="h3 mb-0">Analytics</div>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-sm">
            <i class="gd-arrow-left mr-1"></i> Back to Dashboard
        </a>
    </div>
</div>

<!-- Daily Stats Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Daily Activity (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="dailyStatsChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Cities Performance -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Top Cities by Services</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>City</th>
                                <th>Services</th>
                                <th>Categories</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCities as $city)
                                <tr>
                                    <td>{{ $city->name ?? 'City #' . $city->id }}</td>
                                    <td>
                                        <span class="badge badge-primary">{{ $city->services_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $city->categories_count }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Category Performance -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Category Performance</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Services</th>
                                <th>Avg Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categoryPerformance as $category)
                                <tr>
                                    <td>{{ Str::limit($category['name'], 25) }}</td>
                                    <td>
                                        <span class="badge badge-primary">{{ $category['services_count'] }}</span>
                                    </td>
                                    <td>
                                        @if($category['average_rating'] > 0)
                                            <span class="badge badge-success">{{ $category['average_rating'] }} ⭐</span>
                                        @else
                                            <span class="text-muted">No ratings</span>
                                        @endif
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

<!-- User Growth Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">User Growth Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="userGrowthChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart configurations
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        }
    };

    // Daily Stats Chart
    const dailyStatsCtx = document.getElementById('dailyStatsChart').getContext('2d');
    new Chart(dailyStatsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyStats->pluck('date')) !!},
            datasets: [
                {
                    label: 'New Users',
                    data: {!! json_encode($dailyStats->pluck('users')) !!},
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4
                },
                {
                    label: 'New Services',
                    data: {!! json_encode($dailyStats->pluck('services')) !!},
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.4
                }
            ]
        },
        options: {
            ...chartOptions,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(userGrowthCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($userGrowth->pluck('date')) !!},
            datasets: [{
                label: 'Daily Registrations',
                data: {!! json_encode($userGrowth->pluck('count')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

@endsection