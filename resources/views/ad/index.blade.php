@extends('layouts.grain')

@section('title', 'Ads')

@section('content')

@include('components.notification')

<div id="feedback-container"></div>

<div class="card mb-3 mb-md-4">
	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item active" aria-current="page">Ads</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">Ads</div>
			<a href="{{ route('ad.create') }}" class="btn btn-primary">
				<i class="gd-plus"></i> Add New
			</a>
		</div>

		<!-- Filters -->
		<div class="card mb-3">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h6 class="mb-0">Search & Filters</h6>
				<button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#filterSection" aria-expanded="true" aria-controls="filterSection">
					<i class="gd-angle-down"></i> Toggle
				</button>
			</div>
			<div class="collapse show" id="filterSection">
				<div class="card-body">
					<form method="GET" action="{{ route('ad.index') }}">
						<div class="row">
							<div class="col-md-2">
								<label for="city_id" class="form-label">City</label>
								<select name="city_id" class="form-control" id="city_id">
									<option value="">All Cities</option>
									@foreach($cities as $city)
										<option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
											{{ $city->name }}
										</option>
									@endforeach
								</select>
							</div>
							<div class="col-md-2">
								<label for="category_id" class="form-label">Category</label>
								<select name="category_id" class="form-control" id="category_id">
									<option value="">All Categories</option>
									@foreach($categories as $category)
										<option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
											{{ $category->name }}
										</option>
									@endforeach
								</select>
							</div>
							<div class="col-md-2">
								<label for="service_id" class="form-label">Service</label>
								<select name="service_id" class="form-control" id="service_id">
									<option value="">All Services</option>
									@foreach($services as $service)
										<option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
											{{ $service->name }}
										</option>
									@endforeach
								</select>
							</div>
							<div class="col-md-2">
								<label for="location" class="form-label">Location</label>
								<select name="location" class="form-control" id="location">
									<option value="">All Locations</option>
									<option value="home" {{ request('location') == 'home' ? 'selected' : '' }}>Home</option>
									<option value="category_profile" {{ request('location') == 'category_profile' ? 'selected' : '' }}>Category Profile</option>
									<option value="service_profile" {{ request('location') == 'service_profile' ? 'selected' : '' }}>Service Profile</option>
									<option value="all_locations" {{ request('location') == 'all_locations' ? 'selected' : '' }}>All Locations</option>
								</select>
							</div>
							<div class="col-md-2">
								<label for="status" class="form-label">Status</label>
								<select name="status" class="form-control" id="status">
									<option value="">All Status</option>
									<option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
									<option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
								</select>
							</div>
							<div class="col-md-2 d-flex align-items-end">
								<button type="submit" class="btn btn-primary btn-block">Filter</button>
							</div>
						</div>

						<div class="row mt-2">
							<div class="col-md-10">
								<label for="search" class="form-label">Search</label>
								<input type="text" name="search" class="form-control" id="search" placeholder="Search by name, category, or service..." value="{{ request('search') }}">
							</div>
							<div class="col-md-2 d-flex align-items-end">
								<a href="{{ route('ad.index') }}" class="btn btn-outline-secondary btn-block">Clear</a>
							</div>
						</div>

						<!-- Active Filters Display -->
						@if(request()->hasAny(['search', 'city_id', 'category_id', 'service_id', 'location', 'status']))
						<div class="row mt-3">
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
											<span class="badge badge-success mr-1 mb-1">Category: {{ $categories->find(request('category_id'))->name ?? 'Unknown' }}</span>
										@endif
										@if(request('service_id'))
											<span class="badge badge-primary mr-1 mb-1">Service: {{ $services->find(request('service_id'))->name ?? 'Unknown' }}</span>
										@endif
										@if(request('location'))
											@php
												$locationLabels = [
													'home' => 'Home',
													'category_profile' => 'Category Profile',
													'service_profile' => 'Service Profile',
													'all_locations' => 'All Locations'
												];
											@endphp
											<span class="badge badge-warning mr-1 mb-1">Location: {{ $locationLabels[request('location')] ?? request('location') }}</span>
										@endif
										@if(request('status'))
											<span class="badge badge-{{ request('status') == 'active' ? 'success' : 'danger' }} mr-1 mb-1">{{ ucfirst(request('status')) }}</span>
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
		<!-- End Filters -->

		<!-- Results Info -->
		<div class="d-flex justify-content-between align-items-center mb-3">
			<div>
				<small class="text-muted">
					Showing {{ $ads->firstItem() ?? 0 }} to {{ $ads->lastItem() ?? 0 }} of {{ $ads->total() }} results
					@if(request()->hasAny(['search', 'city_id', 'category_id', 'service_id', 'location', 'status']))
						<span class="badge badge-info ml-1">Filtered</span>
					@endif
				</small>
			</div>
		</div>

		<!-- Bulk Actions -->
		<form id="bulk-form" method="POST" action="{{ route('ad.bulk-action') }}">
			@csrf
			<div class="d-flex justify-content-between align-items-center mb-3">
				<div>
					<select id="bulk-action" name="action" class="form-control d-inline-block" style="width: auto;">
						<option value="">Bulk Actions</option>
						<option value="delete">Delete</option>
					</select>
					<button type="submit" class="btn btn-secondary btn-sm ml-2" onclick="return confirmBulkAction()">Apply</button>
				</div>
				<div>
					<input type="checkbox" id="select-all"> <label for="select-all">Select All</label>
				</div>
			</div>

			<!-- Ads -->
			<div class="table-responsive-xl">
				<table class="table text-nowrap mb-0">
					<thead>
					<tr>
						<th class="font-weight-semi-bold border-top-0 py-2" style="width: 50px;">
							<input type="checkbox" id="select-all-header">
						</th>
						<th class="font-weight-semi-bold border-top-0 py-2">#</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Image</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Name</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Location</th>
						<th class="font-weight-semi-bold border-top-0 py-2">City</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Category</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Service</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Link</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Status</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Created Date</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
					</tr>
					</thead>
					<tbody>
					@forelse($ads as $ad)
					<tr id="ad-row-{{ $ad->id }}">
						<td class="py-3">
							<input type="checkbox" name="ad_ids[]" value="{{ $ad->id }}" class="ad-checkbox">
						</td>
						<td class="py-3">{{ $ad->id }}</td>
						<td class="py-3">
							@if($ad->image)
								<img src="{{ asset('storage/' . $ad->image->path) }}" alt="{{ $ad->name }}" class="rounded" width="50" height="50">
							@else
								<span class="avatar-placeholder bg-secondary text-white rounded d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
									{{ substr($ad->name, 0, 1) }}
								</span>
							@endif
						</td>
						<td class="align-middle py-3">
							<div class="d-flex align-items-center">
								<div>
									<strong>{{ Str::limit($ad->name, 30) }}</strong>
								</div>
							</div>
						</td>
						<td class="py-3">
							@php
								$locationLabels = [
									'home' => 'Home',
									'category_profile' => 'Category Profile',
									'service_profile' => 'Service Profile',
									'all_locations' => 'All Locations'
								];
								$locationColors = [
									'home' => 'primary',
									'category_profile' => 'success',
									'service_profile' => 'info',
									'all_locations' => 'warning'
								];
							@endphp
							<span class="badge badge-{{ $locationColors[$ad->location] ?? 'secondary' }}">
								{{ $locationLabels[$ad->location] ?? $ad->location }}
							</span>
						</td>
						<td class="py-3">
							<span class="badge badge-info">{{ $ad->city->name }}</span>
						</td>
						<td class="py-3">
							@if($ad->category)
								<span class="badge badge-success">{{ Str::limit($ad->category->name, 20) }}</span>
							@else
								<span class="text-muted font-italic">None</span>
							@endif
						</td>
						<td class="py-3">
							@if($ad->service)
								<span class="badge badge-primary">{{ Str::limit($ad->service->name, 20) }}</span>
							@else
								<span class="text-muted font-italic">None</span>
							@endif
						</td>
						<td class="py-3">
							@if($ad->link)
								<a href="{{ $ad->link }}" target="_blank" class="btn btn-sm btn-outline-primary" title="{{ $ad->link }}">
									<i class="gd-link"></i>
								</a>
							@else
								<span class="text-muted font-italic">None</span>
							@endif
						</td>
						<td class="py-3">
							@if($ad->isExpired())
								<span class="badge badge-danger">Expired</span>
								@if($ad->expiration_date)
									<br><small class="text-muted">{{ $ad->expiration_date->diffForHumans() }}</small>
								@endif
							@else
								<span class="badge badge-success">Active</span>
								@if($ad->expiration_date)
									<br><small class="text-muted">Expires {{ $ad->expiration_date->diffForHumans() }}</small>
								@endif
							@endif
						</td>
						<td class="py-3">{{ $ad->created_at ? $ad->created_at->diffForHumans() : '---' }}</td>
						<td class="py-3">
							<div class="position-relative">
								<a class="link-dark d-inline-block" href="{{ route('ad.show', $ad) }}" title="View Details">
									<i class="gd-eye icon-text"></i>
								</a>
								<a class="link-dark d-inline-block" href="{{ route('ad.edit', $ad) }}" title="Edit">
									<i class="gd-pencil icon-text"></i>
								</a>
								<a class="link-dark d-inline-block" href="#" onclick="destroy(event, {{$ad->id}})" title="Delete">
									<i class="gd-trash icon-text"></i>
								</a>
							</div>
						</td>
					</tr>
					@empty
					<tr>
						<td colspan="12" class="text-center">
							@if(request()->hasAny(['search', 'city_id', 'category_id', 'service_id', 'location', 'status']))
								<strong>No ads found matching your filters</strong><br>
								<small class="text-muted">Try adjusting your search criteria</small><br>
								<a href="{{ route('ad.index') }}" class="btn btn-outline-primary btn-sm mt-2">Clear Filters</a>
							@else
								<strong>No ads found</strong><br>
								<a href="{{ route('ad.create') }}" class="btn btn-primary btn-sm mt-2">Create First Ad</a>
							@endif
						</td>
					</tr>
					@endforelse
					</tbody>
				</table>
				
				{{ $ads->links('components.pagination') }}
				
			</div>
			<!-- End Ads -->
		</form>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality
    const selectAllHeader = document.getElementById('select-all-header');
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.ad-checkbox');
    
    [selectAllHeader, selectAll].forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            selectAllHeader.checked = selectAll.checked = this.checked;
        });
    });
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            selectAllHeader.checked = selectAll.checked = allChecked;
        });
    });
});

function confirmBulkAction() {
    const action = document.getElementById('bulk-action').value;
    const checkedBoxes = document.querySelectorAll('.ad-checkbox:checked');
    
    if (!action) {
        alert('Please select an action');
        return false;
    }
    
    if (checkedBoxes.length === 0) {
        alert('Please select at least one ad');
        return false;
    }
    
    const actionText = action.replace('_', ' ');
    return confirm(`Are you sure you want to ${actionText} ${checkedBoxes.length} ad(s)?`);
}

const alertContainer = document.getElementById('feedback-container');
alertContainer.innerHTML = '';

function destroy(event, id) {
    event.preventDefault();
    
	if(!confirm('Delete this ad? This action cannot be undone.')) {
		return;
	}
    let devID = `ad-row-${id}`;

    const url = `/ads/${id}`;
    const csrf = $('meta[name="csrf-token"]').attr('content');

    let alertClass = '';
    let iconClass = '';
    let title = '';
    let message = '';

    $.ajax({
        method: "DELETE",
        url: url,
        headers: {
            'X-CSRF-TOKEN': csrf
        }
    })
    .done(function( data ) {
        alertClass = 'alert-success';
        iconClass = 'gd-info-circle';
        title = 'Success!';
        message = data.message
        $("#"+devID).remove();
    }).fail(function( err ) {
        alertClass = 'alert-danger';
        iconClass = 'gd-alert';
        title = 'Error!';
        message = err.responseJSON ? err.responseJSON.message : 'An error occurred';
    }).always(function() {
        let alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${iconClass} mr-2"></i>
                <strong>${title}</strong> ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        alertContainer.innerHTML = alertHtml;
        window.scrollTo(0, 0);
    });
}
</script>

@endsection