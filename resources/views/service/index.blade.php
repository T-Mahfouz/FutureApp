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

		<!-- Search and Filters -->
		<div class="card mb-4">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h6 class="mb-0">Search & Filters</h6>
				<button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#filterSection" aria-expanded="true" aria-controls="filterSection">
					<i class="gd-angle-down"></i> Collapse
				</button>
			</div>
			<div class="collapse show" id="filterSection">
				<div class="card-body">
					<form method="GET" action="{{ route('service.index') }}">
						<!-- First Row - Main Filters -->
						<div class="row">
							<div class="col-lg-3 col-md-4 col-sm-6 mb-3">
								<label for="search" class="form-label">Search Services</label>
								<div class="position-relative">
									<input type="text" class="form-control pr-5" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name, description, phone...">
									<div class="position-absolute" style="right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
										<i class="gd-search text-muted"></i>
									</div>
								</div>
							</div>
							<div class="col-lg-2 col-md-4 col-sm-6 mb-3">
								<label for="city_id" class="form-label">City</label>
								<select class="form-control" id="city_id" name="city_id">
									<option value="">All Cities</option>
									@foreach($cities as $city)
										<option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="col-lg-2 col-md-4 col-sm-6 mb-3">
								<label for="category_id" class="form-label">Category</label>
								<select class="form-control" id="category_id" name="category_id">
									<option value="">All Categories</option>
									@foreach($categories as $category)
										<option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="col-lg-2 col-md-4 col-sm-6 mb-3">
								<label for="valid" class="form-label">Status</label>
								<select class="form-control" id="valid" name="valid">
									<option value="">All Status</option>
									<option value="1" {{ request('valid') === '1' ? 'selected' : '' }}>Active</option>
									<option value="0" {{ request('valid') === '0' ? 'selected' : '' }}>Inactive</option>
								</select>
							</div>
							<div class="col-lg-3 col-md-4 col-sm-6 mb-3">
								<label for="service_type" class="form-label">Service Type</label>
								<select class="form-control" id="service_type" name="service_type">
									<option value="">All Types</option>
									<option value="main" {{ request('service_type') == 'main' ? 'selected' : '' }}>Main Services</option>
									<option value="sub" {{ request('service_type') == 'sub' ? 'selected' : '' }}>Sub Services</option>
									<option value="ad" {{ request('service_type') == 'ad' ? 'selected' : '' }}>Advertisements</option>
								</select>
							</div>
						</div>

						<!-- Second Row - Advanced Filters -->
						<div class="row">
							<div class="col-lg-2 col-md-3 col-sm-6 mb-3">
								<label for="date_from" class="form-label">From Date</label>
								<input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
							</div>
							<div class="col-lg-2 col-md-3 col-sm-6 mb-3">
								<label for="date_to" class="form-label">To Date</label>
								<input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
							</div>
							<div class="col-lg-2 col-md-3 col-sm-6 mb-3">
								<label for="sort_by" class="form-label">Sort By</label>
								<select class="form-control" id="sort_by" name="sort_by">
									<option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Created Date</option>
									<option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
									<option value="arrangement_order" {{ request('sort_by') == 'arrangement_order' ? 'selected' : '' }}>Display Order</option>
									<option value="rates_count" {{ request('sort_by') == 'rates_count' ? 'selected' : '' }}>Ratings Count</option>
									<option value="favorites_count" {{ request('sort_by') == 'favorites_count' ? 'selected' : '' }}>Favorites Count</option>
								</select>
							</div>
							<div class="col-lg-2 col-md-3 col-sm-6 mb-3">
								<label for="sort_direction" class="form-label">Direction</label>
								<select class="form-control" id="sort_direction" name="sort_direction">
									<option value="desc" {{ request('sort_direction') == 'desc' ? 'selected' : '' }}>Descending</option>
									<option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>Ascending</option>
								</select>
							</div>
							<div class="col-lg-2 col-md-6 col-sm-6 mb-3 d-flex align-items-end">
								<button type="submit" class="btn btn-primary btn-block">
									<i class="gd-search"></i> Search
								</button>
							</div>
							<div class="col-lg-2 col-md-6 col-sm-6 mb-3 d-flex align-items-end">
								<a href="{{ route('service.index') }}" class="btn btn-outline-secondary btn-block">
									<i class="gd-reload"></i> Clear All
								</a>
							</div>
						</div>

						<!-- Active Filters Display -->
						@if(request()->hasAny(['search', 'city_id', 'category_id', 'valid', 'service_type', 'date_from', 'date_to']))
						<div class="row">
							<div class="col-12">
								<div class="border-top pt-3">
									<small class="text-muted">Active filters:</small>
									<div class="mt-2">
										@if(request('search'))
											<span class="badge badge-primary mr-1 mb-1">Search: "{{ request('search') }}"</span>
										@endif
										@if(request('city_id'))
											<span class="badge badge-info mr-1 mb-1">City: {{ $cities->find(request('city_id'))->name ?? 'Unknown' }}</span>
										@endif
										@if(request('category_id'))
											<span class="badge badge-secondary mr-1 mb-1">Category: {{ $categories->find(request('category_id'))->name ?? 'Unknown' }}</span>
										@endif
										@if(request('valid') !== null)
											<span class="badge badge-{{ request('valid') == '1' ? 'success' : 'danger' }} mr-1 mb-1">
												{{ request('valid') == '1' ? 'Active' : 'Inactive' }}
											</span>
										@endif
										@if(request('service_type'))
											<span class="badge badge-warning mr-1 mb-1">
												Type: 
												@if(request('service_type') == 'main')
													Main Services
												@elseif(request('service_type') == 'sub')
													Sub Services
												@elseif(request('service_type') == 'ad')
													Advertisements
												@endif
											</span>
										@endif
										@if(request('date_from'))
											<span class="badge badge-light mr-1 mb-1">From: {{ request('date_from') }}</span>
										@endif
										@if(request('date_to'))
											<span class="badge badge-light mr-1 mb-1">To: {{ request('date_to') }}</span>
										@endif
									</div>
								</div>
							</div>
						</div>
						@endif
					</form>
				</div>
			</div>
		</div>
		<!-- End Search and Filters -->

		<!-- Results Info -->
		<div class="d-flex justify-content-between align-items-center mb-3">
			<div>
				<small class="text-muted">
					Showing {{ $services->firstItem() ?? 0 }} to {{ $services->lastItem() ?? 0 }} of {{ $services->total() }} results
					@if(request()->hasAny(['search', 'city_id', 'category_id', 'valid', 'service_type', 'date_from', 'date_to']))
						<span class="badge badge-info ml-1">Filtered</span>
					@endif
				</small>
			</div>
			@if(request()->hasAny(['search', 'city_id', 'category_id', 'valid', 'service_type', 'date_from', 'date_to']))
			<div>
				<a href="{{ route('service.index') }}" class="btn btn-sm btn-outline-secondary">
					<i class="gd-close"></i> Clear All Filters
				</a>
			</div>
			@endif
		</div>

		<!-- Services -->
		<div class="table-responsive-xl">
			<table class="table text-nowrap mb-0">
				<thead>
				<tr>
					<th class="font-weight-semi-bold border-top-0 py-2">#</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Image</th>
					<th class="font-weight-semi-bold border-top-0 py-2">
						<a href="{{ route('service.index', array_merge(request()->query(), ['sort_by' => 'name', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
							Name
							@if(request('sort_by') == 'name')
								<i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
							@endif
						</a>
					</th>
					<th class="font-weight-semi-bold border-top-0 py-2">City</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Phone</th>
					<th class="font-weight-semi-bold border-top-0 py-2">
						<a href="{{ route('service.index', array_merge(request()->query(), ['sort_by' => 'rates_count', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
							Rating
							@if(request('sort_by') == 'rates_count')
								<i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
							@endif
						</a>
					</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Status</th>
					<th class="font-weight-semi-bold border-top-0 py-2">
						<a href="{{ route('service.index', array_merge(request()->query(), ['sort_by' => 'created_at', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
							Created
							@if(request('sort_by') == 'created_at' || !request('sort_by'))
								<i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
							@endif
						</a>
					</th>
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
								<div class="d-flex align-items-center mb-1">
									<strong class="mr-2">{{ Str::limit($service->name, 30) }}</strong>
									@if($service->valid)
										<span class="badge badge-success badge-pill">Active</span>
									@else
										<span class="badge badge-danger badge-pill">Inactive</span>
									@endif
								</div>
								@if($service->parent_id)
									<small class="text-muted">Sub-service</small>
								@endif
								@if($service->is_add)
									<br><span class="badge badge-info badge-sm">Advertisement</span>
								@endif
							</div>
						</div>
					</td>
					<td class="py-3">
						<span class="badge badge-primary">{{ $service->city->name ?? 'N/A' }}</span>
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
					<td colspan="9" class="text-center py-4">
						@if(request()->hasAny(['search', 'city_id', 'category_id', 'valid', 'service_type', 'date_from', 'date_to']))
							<strong>No services found matching your filters</strong><br>
							<small class="text-muted">Try adjusting your search criteria</small><br>
							<a href="{{ route('service.index') }}" class="btn btn-outline-primary btn-sm mt-2">Clear Filters</a>
						@else
							<strong>No services found</strong><br>
							<a href="{{ route('service.create') }}" class="btn btn-primary btn-sm mt-2">Create First Service</a>
						@endif
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