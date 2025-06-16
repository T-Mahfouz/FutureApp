@extends('layouts.grain')

@section('title', 'Notifications')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">

	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item active" aria-current="page">Notifications</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">Notifications</div>
			<div>
				<a href="{{ route('notification.create') }}" class="btn btn-primary">
					<i class="gd-plus"></i> Add New
				</a>
				<a href="{{ route('notification.send-firebase') }}" class="btn btn-success ml-2">
					<i class="gd-rocket"></i> Send Firebase Only
				</a>
			</div>
		</div>

		<!-- Filter Form -->
		<div class="card mb-3">
			<div class="card-body">
				<form method="GET" action="{{ route('notification.index') }}">
					<div class="form-row">
						<div class="form-group col-md-3">
							<label for="search">Search</label>
							<input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search notifications...">
						</div>
						<div class="form-group col-md-2">
							<label for="type">Type</label>
							<select class="form-control" id="type" name="type">
								<option value="">All Types</option>
								<option value="service" {{ request('type') == 'service' ? 'selected' : '' }}>Service</option>
								<option value="news" {{ request('type') == 'news' ? 'selected' : '' }}>News</option>
							</select>
						</div>
						<div class="form-group col-md-2">
							<label for="city_id">City</label>
							<select class="form-control" id="city_id" name="city_id">
								<option value="">All Cities</option>
								@foreach($cities as $city)
									<option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
								@endforeach
							</select>
						</div>
						<div class="form-group col-md-2">
							<label for="date_from">From Date</label>
							<input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
						</div>
						<div class="form-group col-md-2">
							<label for="date_to">To Date</label>
							<input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
						</div>
						<div class="form-group col-md-1 d-flex align-items-end">
							<button type="submit" class="btn btn-primary btn-block">Filter</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<!-- End Filter Form -->

		<!-- Notifications -->
		<div class="table-responsive-xl">
			<table class="table text-nowrap mb-0">
				<thead>
				<tr>
					<th class="font-weight-semi-bold border-top-0 py-2">
						<input type="checkbox" id="selectAll">
					</th>
					<th class="font-weight-semi-bold border-top-0 py-2">#</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Image</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Title</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Body</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Cities</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Related To</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Created Date</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
				</tr>
				</thead>
				<tbody>
				@forelse($notifications as $notification)
				<tr>
					<td class="py-3">
						<input type="checkbox" name="notification_ids[]" value="{{ $notification->id }}" class="notification-checkbox">
					</td>
					<td class="py-3">{{ $notification->id }}</td>
					<td class="py-3">
						@if($notification->image)
							<img src="{{ asset('storage/' . $notification->image->path) }}" alt="{{ $notification->title }}" class="rounded" width="40" height="40">
						@else
							<span class="avatar-placeholder bg-secondary text-white rounded d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
								<i class="gd-bell"></i>
							</span>
						@endif
					</td>
					<td class="align-middle py-3">
						<div>
							<strong>{{ Str::limit($notification->title, 30) }}</strong>
						</div>
					</td>
					<td class="py-3">
						<span class="text-muted">{{ Str::limit($notification->body, 50) }}</span>
					</td>
					<td class="py-3">
						@if($notification->cities->count() > 0)
							@foreach($notification->cities->take(2) as $city)
								<span class="badge badge-primary mr-1">{{ $city->name }}</span>
							@endforeach
							@if($notification->cities->count() > 2)
								<span class="badge badge-light">+{{ $notification->cities->count() - 2 }} more</span>
							@endif
						@else
							<span class="badge badge-secondary">No Cities</span>
						@endif
					</td>
					<td class="py-3">
						@if($notification->service)
							<span class="badge badge-info">Service: {{ Str::limit($notification->service->name, 20) }}</span>
						@elseif($notification->news)
							<span class="badge badge-success">News: {{ Str::limit($notification->news->name, 20) }}</span>
						@else
							<span class="badge badge-warning">No Content</span>
						@endif
					</td>
					<td class="py-3">{{ $notification->created_at ? $notification->created_at->diffForHumans() : '---' }}</td>
					<td class="py-3">
						<div class="position-relative">
							<a class="link-dark d-inline-block" href="{{ route('notification.show', $notification) }}" title="View Details">
								<i class="gd-eye icon-text"></i>
							</a>
							<a class="link-dark d-inline-block" href="{{ route('notification.edit', $notification) }}" title="Edit">
								<i class="gd-pencil icon-text"></i>
							</a>
							<a class="link-dark d-inline-block" href="#" onclick="if(confirm('Delete this notification? This action cannot be undone.')){document.getElementById('delete-entity-{{ $notification->id }}').submit();return false;}" title="Delete">
								<i class="gd-trash icon-text"></i>
							</a>
							<form id="delete-entity-{{ $notification->id }}" action="{{ route('notification.destroy', $notification) }}" method="POST">
								<input type="hidden" name="_method" value="DELETE">
								@csrf
							</form>
						</div>
					</td>
				</tr>
				@empty
				<tr>
					<td colspan="9" class="text-center">
						<strong>No records found</strong><br>
						<a href="{{ route('notification.create') }}" class="btn btn-primary btn-sm mt-2">Create First Notification</a>
					</td>
				</tr>
				@endforelse

				</tbody>
			</table>

			<!-- Bulk Actions -->
			@if($notifications->count() > 0)
			<div class="mt-3">
				<form id="bulkActionForm" method="POST" action="{{ route('notification.bulk-action') }}">
					@csrf
					<div class="form-row align-items-end">
						<div class="form-group col-md-3">
							<label for="bulk_action">Bulk Action</label>
							<select class="form-control" id="bulk_action" name="action">
								<option value="">Select Action</option>
								<option value="delete">Delete Selected</option>
							</select>
						</div>
						<div class="form-group col-md-3">
							<button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to perform this action on selected notifications?')">
								Apply to Selected
							</button>
						</div>
					</div>
				</form>
			</div>
			@endif
			
			{{ $notifications->links('components.pagination') }}
			
		</div>
		<!-- End Notifications -->
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// Select all checkbox functionality
	const selectAllCheckbox = document.getElementById('selectAll');
	const notificationCheckboxes = document.querySelectorAll('.notification-checkbox');
	
	selectAllCheckbox.addEventListener('change', function() {
		notificationCheckboxes.forEach(checkbox => {
			checkbox.checked = this.checked;
		});
	});

	// Bulk action form submission
	const bulkActionForm = document.getElementById('bulkActionForm');
	if (bulkActionForm) {
		bulkActionForm.addEventListener('submit', function(e) {
			const selectedCheckboxes = document.querySelectorAll('.notification-checkbox:checked');
			
			if (selectedCheckboxes.length === 0) {
				e.preventDefault();
				alert('Please select at least one notification.');
				return false;
			}

			// Add selected IDs to form
			selectedCheckboxes.forEach(checkbox => {
				const hiddenInput = document.createElement('input');
				hiddenInput.type = 'hidden';
				hiddenInput.name = 'notification_ids[]';
				hiddenInput.value = checkbox.value;
				this.appendChild(hiddenInput);
			});
		});
	}
});
</script>

@endsection