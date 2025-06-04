@extends('layouts.grain')

@section('title', 'Ads')

@section('content')

@include('components.notification')

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
				Add new
			</a>
		</div>

		<!-- Filters -->
		<div class="card mb-3">
			<div class="card-body">
				<form method="GET" action="{{ route('ad.index') }}" class="row">
					<div class="col-md-3">
						<select name="city_id" class="form-control">
							<option value="">All Cities</option>
							@foreach($cities as $city)
								<option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
									{{ $city->name }}
								</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-3">
						<select name="location" class="form-control">
							<option value="">All Locations</option>
							<option value="home" {{ request('location') == 'home' ? 'selected' : '' }}>Home</option>
							<option value="category_profile" {{ request('location') == 'category_profile' ? 'selected' : '' }}>Category Profile</option>
							<option value="service_profile" {{ request('location') == 'service_profile' ? 'selected' : '' }}>Service Profile</option>
						</select>
					</div>
					<div class="col-md-4">
						<input type="text" name="search" class="form-control" placeholder="Search by name..." value="{{ request('search') }}">
					</div>
					<div class="col-md-2">
						<button type="submit" class="btn btn-primary btn-block">Filter</button>
					</div>
				</form>
			</div>
		</div>
		<!-- End Filters -->

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
						<th class="font-weight-semi-bold border-top-0 py-2">Created Date</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
					</tr>
					</thead>
					<tbody>
					@forelse($ads as $ad)
					<tr>
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
									<strong>{{ $ad->name }}</strong>
								</div>
							</div>
						</td>
						<td class="py-3">
							@php
								$locationLabels = [
									'home' => 'Home',
									'category_profile' => 'Category Profile',
									'service_profile' => 'Service Profile'
								];
							@endphp
							<span class="badge badge-{{ $ad->location == 'home' ? 'primary' : ($ad->location == 'category_profile' ? 'success' : 'info') }}">
								{{ $locationLabels[$ad->location] ?? $ad->location }}
							</span>
						</td>
						<td class="py-3">
							<span class="badge badge-info">{{ $ad->city->name }}</span>
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
								<a class="link-dark d-inline-block" href="#" onclick="if(confirm('Delete this ad? This action cannot be undone.')){document.getElementById('delete-entity-{{ $ad->id }}').submit();return false;}" title="Delete">
									<i class="gd-trash icon-text"></i>
								</a>
								<form id="delete-entity-{{ $ad->id }}" action="{{ route('ad.destroy', $ad) }}" method="POST" style="display: none;">
									<input type="hidden" name="_method" value="DELETE">
									@csrf
								</form>
							</div>
						</td>
					</tr>
					@empty
					<tr>
						<td colspan="8" class="text-center">
							<strong>No ads found</strong><br>
							<a href="{{ route('ad.create') }}" class="btn btn-primary btn-sm mt-2">Create First Ad</a>
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
            // Sync both select-all checkboxes
            selectAllHeader.checked = selectAll.checked = this.checked;
        });
    });
    
    // Update select-all when individual checkboxes change
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
</script>

@endsection