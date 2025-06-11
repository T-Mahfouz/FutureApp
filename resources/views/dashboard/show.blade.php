@extends('layouts.grain')

@section('title', 'Dashboard')

@section('content')

@include('components.notification')

<div class="mb-3 mb-md-4 d-flex justify-content-between">
    <div class="h3 mb-0">Dashboard</div>
    <div class="text-muted">Welcome back, {{ auth()->user()->name }}!</div>
</div>

<!-- Stats Cards Row -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <h4 class="mb-0">{{ number_format($stats['total_users']) }}</h4>
                    <small>Total Users</small>
                </div>
                <div class="icon icon-lg">
                    <i class="gd-user"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <h4 class="mb-0">{{ number_format($stats['total_services']) }}</h4>
                    <small>Total Services</small>
                </div>
                <div class="icon icon-lg">
                    <i class="gd-layers"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <h4 class="mb-0">{{ number_format($stats['total_cities']) }}</h4>
                    <small>Total Cities</small>
                </div>
                <div class="icon icon-lg">
                    <i class="gd-location"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <h4 class="mb-0">{{ number_format($stats['total_categories']) }}</h4>
                    <small>Categories</small>
                </div>
                <div class="icon icon-lg">
                    <i class="gd-bookmark"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="text-success">{{ number_format($stats['active_services']) }}</h3>
                <p class="mb-0">Active Services</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="text-danger">{{ number_format($stats['inactive_services']) }}</h3>
                <p class="mb-0">Inactive Services</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="text-info">{{ number_format($stats['average_rating'], 1) }}</h3>
                <p class="mb-0">Average Rating</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Monthly Users Chart -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Monthly User Registrations</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyUsersChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Monthly Services Chart -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Monthly Services Created</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyServicesChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Services by City Chart -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Services by City</h5>
            </div>
            <div class="card-body">
                <canvas id="servicesByCityChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Services by Category Chart -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Services by Category</h5>
            </div>
            <div class="card-body">
                <canvas id="servicesByCategoryChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Rating Distribution Chart -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Rating Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="ratingDistributionChart" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Stats</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Total Favorites</small>
                    <h4 class="mb-0">{{ number_format($stats['total_favorites']) }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Total Ratings</small>
                    <h4 class="mb-0">{{ number_format($stats['total_ratings']) }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Contact Messages</small>
                    <h4 class="mb-0">{{ number_format($stats['total_contact_messages']) }}</h4>
                </div>
                <div>
                    <small class="text-muted">News Articles</small>
                    <h4 class="mb-0">{{ number_format($stats['total_news']) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Tables -->
<div class="row">
    <!-- Recent Users -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Users</h5>
            </div>
            <div class="card-body">
                @forelse($recentUsers as $user)
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-sm rounded-circle bg-primary text-white mr-2 d-flex align-items-center justify-content-center">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $user->name }}</h6>
                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No recent users</p>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Recent Services -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Services</h5>
            </div>
            <div class="card-body">
                @forelse($recentServices as $service)
                    <div class="mb-3">
                        <h6 class="mb-1">{{ Str::limit($service->name, 30) }}</h6>
                        <small class="text-muted">
                            {{ $service->city->name ?? 'Unknown City' }} • 
                            {{ $service->created_at->diffForHumans() }}
                        </small>
                    </div>
                @empty
                    <p class="text-muted mb-0">No recent services</p>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Recent Contact Messages -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Contact Messages</h5>
            </div>
            <div class="card-body">
                @forelse($recentContactMessages as $contact)
                    <div class="mb-3">
                        <h6 class="mb-1">{{ $contact->name }}</h6>
                        <p class="mb-1 small">{{ Str::limit($contact->message, 50) }}</p>
                        <small class="text-muted">
                            {{ $contact->city->name ?? 'Unknown City' }} • 
                            {{ $contact->created_at->diffForHumans() }}
                        </small>
                    </div>
                @empty
                    <p class="text-muted mb-0">No recent messages</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Top Rated Services -->
@if($topRatedServices->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top Rated Services</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Service Name</th>
                                <th>City</th>
                                <th>Average Rating</th>
                                <th>Total Ratings</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topRatedServices as $service)
                                <tr>
                                    <td>{{ Str::limit($service['name'], 40) }}</td>
                                    <td>{{ $service['city'] }}</td>
                                    <td>
                                        <span class="badge badge-success">{{ $service['average_rating'] }} ⭐</span>
                                    </td>
                                    <td>{{ $service['total_ratings'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

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

    // Monthly Users Chart
    const monthlyUsersCtx = document.getElementById('monthlyUsersChart').getContext('2d');
    new Chart(monthlyUsersCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyUsers->pluck('month')) !!},
            datasets: [{
                label: 'New Users',
                data: {!! json_encode($monthlyUsers->pluck('count')) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4
            }]
        },
        options: chartOptions
    });

    // Monthly Services Chart
    const monthlyServicesCtx = document.getElementById('monthlyServicesChart').getContext('2d');
    new Chart(monthlyServicesCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($monthlyServices->pluck('month')) !!},
            datasets: [{
                label: 'New Services',
                data: {!! json_encode($monthlyServices->pluck('count')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: chartOptions
    });

    // Services by City Chart
    const servicesByCityCtx = document.getElementById('servicesByCityChart').getContext('2d');
    new Chart(servicesByCityCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($servicesByCity->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($servicesByCity->pluck('count')) !!},
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                ]
            }]
        },
        options: chartOptions
    });

    // Services by Category Chart
    const servicesByCategoryCtx = document.getElementById('servicesByCategoryChart').getContext('2d');
    new Chart(servicesByCategoryCtx, {
        type: 'polarArea',
        data: {
            labels: {!! json_encode($servicesByCategory->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($servicesByCategory->pluck('count')) !!},
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                ]
            }]
        },
        options: chartOptions
    });

    // Rating Distribution Chart
    const ratingDistributionCtx = document.getElementById('ratingDistributionChart').getContext('2d');
    new Chart(ratingDistributionCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($ratingDistribution->pluck('rating')) !!},
            datasets: [{
                label: 'Number of Ratings',
                data: {!! json_encode($ratingDistribution->pluck('count')) !!},
                backgroundColor: [
                    '#FF6B6B', '#FFA726', '#FFEB3B', '#66BB6A', '#42A5F5'
                ],
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