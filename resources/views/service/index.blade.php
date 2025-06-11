@extends('layouts.grain')

@section('title', 'Services')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">
	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item active" aria-current="page">Services</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">Services</div>
			<a href="{{ route('service.create') }}" class="btn btn-primary">
				Add new Service
			</a>
		</div>

		<!-- Filters -->
		<div class="card mb-4">
			<div class="card-body">
				<form method="GET" action="{{ route('service.index') }}">
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label for="search">Search</label>
								<input type="text" class="form-control" id="search" name="search" 
									   value="{{ request('search') }}" placeholder="Search services...">
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label for="city_id">City</label>
								<select class="form-control" id="city_id" name="city_id">
									<option value="">All Cities</option>
									@foreach($cities as $city)
										<option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
											{{ $city->name }}
										</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label for="category_id">Category</label>
								<select class="form-control" id="category_id" name="category_id">
									<option value="">All Categories</option>
									@foreach($categories as $category)
										<option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
											{{ $category->name }}
										</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label for="valid">Status</label>
								<select class="form-control" id="valid" name="valid">
									<option value="">All</option>
									<option value="1" {{ request('valid') === '1' ? 'selected' : '' }}>Active</option>
									<option value="0" {{ request('valid') === '0' ? 'selected' : '' }}>Inactive</option>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>&nbsp;</label>
								<div class="d-flex">
									<button type="submit" class="btn btn-primary mr-2">Filter</button>
									<a href="{{ route('service.index') }}" class="btn btn-secondary">Clear</a>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<!-- End Filters -->

		<!-- Services -->
		<div class="table-responsive-xl">
			<table class="table text-nowrap mb-0">
				<thead>
				<tr>
					<th class="font-weight-semi-bold border-top-0 py-2">#</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Image</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Name</th>
					<th class="font-weight-semi-bold border-top-0 py-2">City</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Phone</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Rating</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Status</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Created</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
				</tr>
				</thead>
				<tbody>
				@forelse($services as $service)
				<tr>
					<td class="py-3">{{ $service->id }}</td>
					<td class="py-3">
						@if($service->image)
							<img src="{{ asset('storage/' . $service->image->path) }}" alt="{{ $service->name }}" 
								 class="rounded" width="40" height="40">
						@else
							<span class="avatar-placeholder bg-secondary text-white rounded d-inline-flex align-items-center justify-content-center" 
								  style="width: 40px; height: 40px;">
								{{ substr($service->name, 0, 1) }}
							</span>
						@endif
					</td>
					<td class="align-middle py-3">
						<div class="d-flex align-items-center">
							<div>
								<strong>{{ Str::limit($service->name, 30) }}</strong>
								@if($service->parent_id)
									<br><small class="text-muted">Sub-service</small>
								@endif
								@if($service->is_add)
									<br><span class="badge badge-info">Advertisement</span>
								@endif
							</div>
						</div>
					</td>
					<td class="py-3">
						<span class="badge badge-secondary">{{ $service->city->name ?? 'N/A' }}</span>
					</td>
					<td class="py-3">{{ $service->phones->first()->phone ?? 'N/A' }}</td>
					<td class="py-3">
						@if($service->rates_count > 0)
							<div class="d-flex align-items-center">
								<span class="text-warning">★</span>
								<span class="ml-1">{{ number_format($service->averageRating(), 1) }}</span>
								<small class="text-muted ml-1">({{ $service->rates_count }})</small>
							</div>
						@else
							<span class="text-muted">No ratings</span>
						@endif
					</td>
					<td class="py-3">
						<span class="badge badge-{{ $service->valid ? 'success' : 'danger' }}">
							{{ $service->valid ? 'Active' : 'Inactive' }}
						</span>
					</td>
					<td class="py-3">{{ $service->created_at->diffForHumans() }}</td>
					<td class="py-3">
						<div class="position-relative">
							<a class="link-dark d-inline-block mr-2" href="{{ route('service.show', $service) }}" title="View Details">
								<i class="gd-eye icon-text"></i>
							</a>
							<a class="link-dark d-inline-block mr-2" href="{{ route('service.edit', $service) }}" title="Edit">
								<i class="gd-pencil icon-text"></i>
							</a>
							<button class="btn btn-link link-dark p-0 mr-2" onclick="toggleStatus({{ $service->id }})" title="Toggle Status">
								<i class="gd-reload icon-text"></i>
							</button>
							<a class="link-dark d-inline-block" href="#" 
							   onclick="if(confirm('Delete this service? This action cannot be undone.')){document.getElementById('delete-entity-{{ $service->id }}').submit();return false;}" 
							   title="Delete">
								<i class="gd-trash icon-text"></i>
							</a>
							<form id="delete-entity-{{ $service->id }}" action="{{ route('service.destroy', $service) }}" method="POST">
								<input type="hidden" name="_method" value="DELETE">
								@csrf
							</form>
						</div>
					</td>
				</tr>
				@empty
				<tr>
					<td colspan="9" class="text-center">
						<strong>No services found</strong><br>
						<a href="{{ route('service.create') }}" class="btn btn-primary btn-sm mt-2">Create First Service</a>
					</td>
				</tr>
				@endforelse
				</tbody>
			</table>
			
			{{ $services->links('components.pagination') }}
		</div>
		<!-- End Services -->
	</div>
</div>

<script>
function toggleStatus(serviceId) {
    if(confirm('Are you sure you want to toggle the status of this service?')) {
        fetch(`/services/${serviceId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Error updating status');
            }
        })
        .catch(error => {
            alert('Error updating status');
        });
    }
}
</script>

@endsection