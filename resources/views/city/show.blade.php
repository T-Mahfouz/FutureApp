@extends('layouts.grain')

@section('title', 'City Details')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">
	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('city.index') }}">Cities</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ $city->name }}</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
			<div class="d-flex align-items-center">
				@if($city->image)
					<img src="{{ asset('storage/' . $city->image->path) }}" alt="{{ $city->name }}" class="rounded mr-3" width="60" height="60">
				@endif
				<div>
					<h3 class="mb-0">{{ $city->name }}</h3>
					<small class="text-muted">Created {{ $city->created_at ? $city->created_at->format('M d, Y') : 'Unknown' }}</small>
				</div>
			</div>
			<div>
				<a href="{{ route('city.edit', $city) }}" class="btn btn-primary">
					<i class="gd-pencil"></i> Edit City
				</a>
			</div>
		</div>

		<!-- City Statistics -->
		<div class="row mb-4">
			<div class="col-md-3">
				<div class="card bg-info text-white">
					<div class="card-body text-center">
						<h4>{{ $city->services()->count() }}</h4>
						<small>Services</small>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card bg-success text-white">
					<div class="card-body text-center">
						<h4>{{ $city->categories()->count() }}</h4>
						<small>Categories</small>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card bg-warning text-white">
					<div class="card-body text-center">
						<h4>{{ $city->admins()->count() }}</h4>
						<small>Admins</small>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card bg-secondary text-white">
					<div class="card-body text-center">
						<h4>{{ $city->users()->count() }}</h4>
						<small>Users</small>
					</div>
				</div>
			</div>
		</div>

		<!-- City Configuration -->
		@if($city->config)
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">Firebase Configuration</h5>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-6">
						<strong>Firebase Topic:</strong>
						<p class="text-muted">{{ $city->config->firebase_topic ?? 'Not configured' }}</p>
					</div>
					<div class="col-md-6">
						<strong>Firebase Token:</strong>
						<p class="text-muted">{{ $city->config->firebase_token ? 'Configured' : 'Not configured' }}</p>
					</div>
				</div>
			</div>
		</div>
		@endif

		<!-- Assigned Admins -->
		@if($city->admins->count() > 0)
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">Assigned Admins ({{ $city->admins->count() }})</h5>
			</div>
			<div class="card-body">
				<div class="row">
					@foreach($city->admins as $admin)
					<div class="col-md-6 mb-3">
						<div class="d-flex align-items-center">
							@if($admin->image)
								<img src="{{ asset('storage/' . $admin->image->path) }}" alt="{{ $admin->name }}" class="rounded-circle mr-3" width="40" height="40">
							@else
								<span class="avatar-placeholder mr-3">{{ substr($admin->name, 0, 1) }}</span>
							@endif
							<div>
								<strong>{{ $admin->name }}</strong>
								<br><small class="text-muted">{{ $admin->email }}</small>
							</div>
						</div>
					</div>
					@endforeach
				</div>
			</div>
		</div>
		@endif

		<!-- Recent Services -->
		@if($city->services->count() > 0)
		<div class="card mb-4">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0">Recent Services ({{ $city->services->count() }} total)</h5>
				<small class="text-muted">Showing latest 5</small>
			</div>
			<div class="card-body">
				@foreach($city->services()->latest()->take(5)->get() as $service)
				<div class="d-flex align-items-center mb-3">
					@if($service->image)
						<img src="{{ asset('storage/' . $service->image->path) }}" alt="{{ $service->name }}" class="rounded mr-3" width="40" height="40">
					@else
						<span class="avatar-placeholder mr-3">{{ substr($service->name, 0, 1) }}</span>
					@endif
					<div class="flex-grow-1">
						<strong>{{ $service->name }}</strong>
						<br><small class="text-muted">{{ Str::limit($service->brief_description, 60) }}</small>
					</div>
					<div class="text-right">
						<small class="text-muted">{{ $service->created_at->diffForHumans() }}</small>
					</div>
				</div>
				@endforeach
			</div>
		</div>
		@endif

		<!-- Recent Categories -->
		@if($city->categories->count() > 0)
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0">Categories ({{ $city->categories->count() }} total)</h5>
			</div>
			<div class="card-body">
				<div class="row">
					@foreach($city->categories()->latest()->take(8)->get() as $category)
					<div class="col-md-6 mb-2">
						<div class="d-flex align-items-center">
							@if($category->image)
								<img src="{{ asset('storage/' . $category->image->path) }}" alt="{{ $category->name }}" class="rounded mr-2" width="30" height="30">
							@else
								<span class="badge badge-secondary mr-2">{{ substr($category->name, 0, 1) }}</span>
							@endif
							<span>{{ $category->name }}</span>
							@if($category->parent_id)
								<small class="text-muted ml-2">(Sub-category)</small>
							@endif
						</div>
					</div>
					@endforeach
				</div>
			</div>
		</div>
		@endif

	</div>
</div>
@endsection