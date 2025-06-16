@extends('layouts.grain')

@section('title', 'Service Requests')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">
	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('service.index') }}">Services</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">Service Requests</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">Service Requests</div>
			<div>
				<a href="{{ route('service.index') }}" class="btn btn-secondary mr-2">
					<i class="gd-arrow-left"></i> Back to Services
				</a>
			</div>
		</div>

		<!-- Search and Filters -->
		<div class="card mb-4">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h6 class="mb-0">Search & Filters</h6>
				<button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#filterSection" aria-expanded="true" aria-controls="filterSection">
					<i class="gd-angle-down"></i> Toggle
				</button>
			</div>
			<div class="collapse show" id="filterSection">
				<div class="card-body">
					<form method="GET" action="{{ route('service.requests') }}">
						<div class="row">
							<div class="col-md-3 mb-3">
								<label for="search" class="form-label">Search</label>
								<input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search by service name, user...">
							</div>
							<div class="col-md-2 mb-3">
								<label for="city_id" class="form-label">City</label>
								<select class="form-control" id="city_id" name="city_id">
									<option value="">All Cities</option>
									@foreach($cities as $city)
										<option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="col-md-2 mb-3">
								<label for="request_status" class="form-label">Status</label>
								<select class="form-control" id="request_status" name="request_status">
									<option value="pending" {{ request('request_status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
									<option value="approved" {{ request('request_status') == 'approved' ? 'selected' : '' }}>Approved</option>
									<option value="rejected" {{ request('request_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
									<option value="" {{ request('request_status') == '' ? 'selected' : '' }}>All Status</option>
								</select>
							</div>
							<div class="col-md-2 mb-3">
								<label for="date_from" class="form-label">From Date</label>
								<input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
							</div>
							<div class="col-md-2 mb-3">
								<label for="date_to" class="form-label">To Date</label>
								<input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
							</div>
							<div class="col-md-1 mb-3 d-flex align-items-end">
								<button type="submit" class="btn btn-primary btn-block">
									<i class="gd-search"></i>
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>

		<!-- Results Info -->
		<div class="d-flex justify-content-between align-items-center mb-3">
			<div>
				<small class="text-muted">
					Showing {{ $services->firstItem() ?? 0 }} to {{ $services->lastItem() ?? 0 }} of {{ $services->total() }} requests
				</small>
			</div>
		</div>

		<!-- Bulk Actions -->
		<form id="bulk-form" method="POST" action="{{ route('service.bulk-action') }}">
			@csrf
			<div class="d-flex justify-content-between align-items-center mb-3">
				<div>
					<select id="bulk-action" name="action" class="form-control d-inline-block" style="width: auto;">
						<option value="">Bulk Actions</option>
						<option value="approve">Approve Selected</option>
						<option value="reject">Reject Selected</option>
					</select>
					<button type="submit" class="btn btn-secondary btn-sm ml-2" onclick="return confirmBulkAction()">Apply</button>
				</div>
				<div>
					<input type="checkbox" id="select-all"> <label for="select-all">Select All</label>
				</div>
			</div>

			<!-- Service Requests Table -->
			<div class="table-responsive-xl">
				<table class="table text-nowrap mb-0">
					<thead>
					<tr>
						<th class="font-weight-semi-bold border-top-0 py-2" style="width: 50px;">
							<input type="checkbox" id="select-all-header">
						</th>
						<th class="font-weight-semi-bold border-top-0 py-2">#</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Service</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Requested By</th>
						<th class="font-weight-semi-bold border-top-0 py-2">City</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Status</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Requested Date</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
					</tr>
					</thead>
					<tbody>
					@forelse($services as $service)
					<tr id="service-row-{{ $service->id }}">
						<td class="py-3">
							@if($service->isPending())
								<input type="checkbox" name="service_ids[]" value="{{ $service->id }}" class="service-checkbox">
							@endif
						</td>
						<td class="py-3">{{ $service->id }}</td>
						<td class="py-3">
							<div class="d-flex align-items-center">
								@if($service->image)
									<img src="{{ asset('storage/' . $service->image->path) }}" alt="{{ $service->name }}" class="rounded mr-3" width="50" height="50">
								@else
									<span class="avatar-placeholder bg-secondary text-white rounded d-inline-flex align-items-center justify-content-center mr-3" style="width: 50px; height: 50px;">
										{{ substr($service->name, 0, 1) }}
									</span>
								@endif
								<div>
									<strong>{{ $service->name }}</strong>
									@if($service->brief_description)
										<br><small class="text-muted">{{ Str::limit($service->brief_description, 60) }}</small>
									@endif
								</div>
							</div>
						</td>
						<td class="py-3">
							@if($service->user)
								<div>
									<strong>{{ $service->user->name }}</strong>
									<br><small class="text-muted">{{ $service->user->email }}</small>
								</div>
							@else
								<span class="text-muted">Unknown User</span>
							@endif
						</td>
						<td class="py-3">
							<span class="badge badge-primary">{{ $service->city->name ?? 'N/A' }}</span>
						</td>
						<td class="py-3">
							@if($service->isPending())
								<span class="badge badge-warning">Pending</span>
							@elseif($service->isApproved())
								<span class="badge badge-success">Approved</span>
								@if($service->approvedBy)
									<br><small class="text-muted">by {{ $service->approvedBy->name }}</small>
								@endif
							@elseif($service->isRejected())
								<span class="badge badge-danger">Rejected</span>
								@if($service->rejectedBy)
									<br><small class="text-muted">by {{ $service->rejectedBy->name }}</small>
								@endif
							@endif
						</td>
						<td class="py-3">
							{{ $service->requested_at ? $service->requested_at->format('M d, Y') : ($service->created_at ? $service->created_at->format('M d, Y') : 'N/A') }}
							<br><small class="text-muted">{{ $service->requested_at ? $service->requested_at->diffForHumans() : ($service->created_at ? $service->created_at->diffForHumans() : '') }}</small>
						</td>
						<td class="py-3">
							<div class="position-relative">
								<a class="link-dark d-inline-block mr-2" href="{{ route('service.show', $service) }}" title="View Details">
									<i class="gd-eye icon-text"></i>
								</a>
								
								@if($service->isPending())
									<button class="btn btn-link link-success p-0 mr-2" onclick="approveService({{ $service->id }})" title="Approve">
										<i class="gd-check icon-text"></i>
									</button>
									<button class="btn btn-link link-danger p-0 mr-2" onclick="rejectService({{ $service->id }})" title="Reject">
										<i class="gd-close icon-text"></i>
									</button>
								@endif
								
								@if($service->isRejected() && $service->rejection_reason)
									<button class="btn btn-link link-info p-0 mr-2" onclick="showRejectionReason('{{ addslashes($service->rejection_reason) }}')" title="View Rejection Reason">
										<i class="gd-info-circle icon-text"></i>
									</button>
								@endif
							</div>
						</td>
					</tr>
					@empty
					<tr>
						<td colspan="8" class="text-center py-4">
							<strong>No service requests found</strong><br>
							<small class="text-muted">No requests match your current filters</small>
						</td>
					</tr>
					@endforelse
					</tbody>
				</table>
				
				{{ $services->links('components.pagination') }}
			</div>
		</form>
	</div>
</div>

<!-- Rejection Reason Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Rejection Reason</h5>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p id="rejection-reason-text"></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality
    const selectAllHeader = document.getElementById('select-all-header');
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.service-checkbox');
    
    [selectAllHeader, selectAll].forEach(checkbox => {
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                if (selectAllHeader) selectAllHeader.checked = this.checked;
                if (selectAll) selectAll.checked = this.checked;
            });
        }
    });
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            if (selectAllHeader) selectAllHeader.checked = allChecked;
            if (selectAll) selectAll.checked = allChecked;
        });
    });
});

function approveService(serviceId) {
    if(confirm('Are you sure you want to approve this service request?')) {
        fetch(`/services/${serviceId}/approve`, {
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
                alert(data.message || 'Error approving service');
            }
        })
        .catch(error => {
            alert('Error approving service');
        });
    }
}

function rejectService(serviceId) {
    const reason = prompt('Please provide a reason for rejection:');
    if(reason && reason.trim()) {
        fetch(`/services/${serviceId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                rejection_reason: reason.trim()
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error rejecting service');
            }
        })
        .catch(error => {
            alert('Error rejecting service');
        });
    }
}

function showRejectionReason(reason) {
    document.getElementById('rejection-reason-text').textContent = reason;
    $('#rejectionModal').modal('show');
}

function confirmBulkAction() {
    const action = document.getElementById('bulk-action').value;
    const checkedBoxes = document.querySelectorAll('.service-checkbox:checked');
    
    if (!action) {
        alert('Please select an action');
        return false;
    }
    
    if (checkedBoxes.length === 0) {
        alert('Please select at least one service');
        return false;
    }
    
    if (action === 'reject') {
        const reason = prompt('Please provide a reason for rejection:');
        if (!reason || !reason.trim()) {
            return false;
        }
        
        // Add hidden input for rejection reason
        const form = document.getElementById('bulk-form');
        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'rejection_reason';
        reasonInput.value = reason.trim();
        form.appendChild(reasonInput);
    }
    
    const actionText = action === 'approve' ? 'approve' : 'reject';
    return confirm(`Are you sure you want to ${actionText} ${checkedBoxes.length} service(s)?`);
}
</script>

@endsection