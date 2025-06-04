@extends('layouts.grain')

@section('title', 'Contact Messages')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">
	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item active" aria-current="page">Contact Messages</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">Contact Messages</div>
		</div>

		<!-- Filters -->
		<div class="card mb-3">
			<div class="card-body">
				<form method="GET" action="{{ route('contact.index') }}" class="row">
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
						<select name="status" class="form-control">
							<option value="">All Status</option>
							<option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
							<option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread</option>
						</select>
					</div>
					<div class="col-md-4">
						<input type="text" name="search" class="form-control" placeholder="Search by name, phone, or message..." value="{{ request('search') }}">
					</div>
					<div class="col-md-2">
						<button type="submit" class="btn btn-primary btn-block">Filter</button>
					</div>
				</form>
			</div>
		</div>
		<!-- End Filters -->

		<!-- Bulk Actions -->
		<form id="bulk-form" method="POST" action="{{ route('contact.bulk-action') }}">
			@csrf
			<div class="d-flex justify-content-between align-items-center mb-3">
				<div>
					<select id="bulk-action" name="action" class="form-control d-inline-block" style="width: auto;">
						<option value="">Bulk Actions</option>
						<option value="mark_read">Mark as Read</option>
						<option value="mark_unread">Mark as Unread</option>
						<option value="delete">Delete</option>
					</select>
					<button type="submit" class="btn btn-secondary btn-sm ml-2" onclick="return confirmBulkAction()">Apply</button>
				</div>
				<div>
					<input type="checkbox" id="select-all"> <label for="select-all">Select All</label>
				</div>
			</div>

			<!-- Contact Messages -->
			<div class="table-responsive-xl">
				<table class="table text-nowrap mb-0">
					<thead>
					<tr>
						<th class="font-weight-semi-bold border-top-0 py-2" style="width: 50px;">
							<input type="checkbox" id="select-all-header">
						</th>
						<th class="font-weight-semi-bold border-top-0 py-2">#</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Status</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Name</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Phone</th>
						<th class="font-weight-semi-bold border-top-0 py-2">City</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Message</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Date</th>
						<th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
					</tr>
					</thead>
					<tbody>
					@forelse($contacts as $contact)
					<tr class="{{ $contact->is_read ? '' : 'table-warning' }}">
						<td class="py-3">
							<input type="checkbox" name="contact_ids[]" value="{{ $contact->id }}" class="contact-checkbox">
						</td>
						<td class="py-3">
							<div class="d-flex align-items-center">
								@if($contact->is_read)
									<i class="gd-envelope-open text-success mr-2" title="Read"></i>
								@else
									<i class="gd-envelope text-warning mr-2" title="Unread"></i>
								@endif
								{{ $contact->id }}
							</div>
						</td>
						<td class="py-3">
							@if($contact->is_read)
								<span class="badge badge-success"><i class="gd-check"></i> Read</span>
							@else
								<span class="badge badge-warning"><i class="gd-time"></i> Unread</span>
							@endif
						</td>
						<td class="align-middle py-3">
							<div class="d-flex align-items-center">
								@if($contact->is_read)
									<i class="gd-envelope-open text-muted mr-2" title="Read message"></i>
								@else
									<i class="gd-envelope text-primary mr-2" title="Unread message"></i>
								@endif
								<div>
									<strong class="{{ $contact->is_read ? 'text-muted' : '' }}">{{ $contact->name }}</strong>
									@if($contact->user)
										<br><small class="text-muted">Registered User</small>
									@else
										<br><small class="text-muted">Guest</small>
									@endif
								</div>
							</div>
						</td>
						<td class="py-3">
							<a href="tel:{{ $contact->phone }}" class="text-decoration-none">{{ $contact->phone }}</a>
						</td>
						<td class="py-3">
							<span class="badge badge-info">{{ $contact->city->name }}</span>
						</td>
						<td class="py-3" style="max-width: 300px;">
							{{ Str::limit($contact->message, 60) }}
						</td>
						<td class="py-3">{{ $contact->created_at ? $contact->created_at->diffForHumans() : '---' }}</td>
						<td class="py-3">
							<div class="position-relative">
								<a class="link-dark d-inline-block" href="{{ route('contact.show', $contact) }}" title="View Details">
									<i class="gd-eye icon-text"></i>
								</a>
								
								<a class="link-dark d-inline-block" href="#" onclick="toggleRead({{ $contact->id }})" title="{{ $contact->is_read ? 'Mark as Unread' : 'Mark as Read' }}">
									<i class="gd-reload icon-text"></i>
								</a>
								
								<a class="link-dark d-inline-block" href="#" onclick="if(confirm('Delete this message? This action cannot be undone.')){document.getElementById('delete-entity-{{ $contact->id }}').submit();return false;}" title="Delete">
									<i class="gd-trash icon-text"></i>
								</a>
								<form id="delete-entity-{{ $contact->id }}" action="{{ route('contact.destroy', $contact) }}" method="POST" style="display: none;">
									<input type="hidden" name="_method" value="DELETE">
									@csrf
								</form>
								
								<!-- Hidden form for toggle read -->
								<form id="toggle-read-{{ $contact->id }}" action="{{ route('contact.toggle-read', $contact) }}" method="POST" style="display: none;">
									@csrf
								</form>
							</div>
						</td>
					</tr>
					@empty
					<tr>
						<td colspan="9" class="text-center">
							<strong>No contact messages found</strong><br>
							<small class="text-muted">Contact messages will appear here when users submit them</small>
						</td>
					</tr>
					@endforelse
					</tbody>
				</table>
				
				{{ $contacts->links('components.pagination') }}
				
			</div>
			<!-- End Contact Messages -->
		</form>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality
    const selectAllHeader = document.getElementById('select-all-header');
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.contact-checkbox');
    
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
            const someChecked = Array.from(checkboxes).some(cb => cb.checked);
            selectAllHeader.checked = selectAll.checked = allChecked;
        });
    });
});

function confirmBulkAction() {
    const action = document.getElementById('bulk-action').value;
    const checkedBoxes = document.querySelectorAll('.contact-checkbox:checked');
    
    if (!action) {
        alert('Please select an action');
        return false;
    }
    
    if (checkedBoxes.length === 0) {
        alert('Please select at least one message');
        return false;
    }
    
    const actionText = action.replace('_', ' ');
    return confirm(`Are you sure you want to ${actionText} ${checkedBoxes.length} message(s)?`);
}

function toggleRead(id) {
    if(confirm('Are you sure you want to toggle the status of this message?')) {
        document.getElementById('toggle-read-' + id).submit();
        return false;
    }
}
</script>

@endsection

<style>
.table-warning {
    background-color: rgba(255, 243, 205, 0.3) !important;
}

.table tr.table-warning:hover {
    background-color: rgba(255, 243, 205, 0.5) !important;
}

.table tr:not(.table-warning) .contact-name {
    color: #6c757d;
}

.unread-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #007bff;
    display: inline-block;
    margin-right: 8px;
}
</style>