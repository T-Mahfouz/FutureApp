@extends('layouts.grain')

@section('title', 'Notifications')

@section('content')

@include('components.notification')

{{-- Modal z-index fix --}}
<style>
.notification-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9998 !important;
    display: none;
}
.notification-modal-backdrop.show {
    display: block;
}
.notification-custom-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999 !important;
    display: none;
    overflow-x: hidden;
    overflow-y: auto;
    outline: 0;
}
.notification-custom-modal.show {
    display: block;
}
.notification-custom-modal .modal-dialog {
    position: relative;
    margin: 1.75rem auto;
    max-width: 500px;
    pointer-events: none;
}
.notification-custom-modal.modal-dialog-centered .modal-dialog,
.notification-custom-modal .modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 3.5rem);
}
.notification-custom-modal .modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    pointer-events: auto;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 0.3rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.5);
    outline: 0;
}
.notification-custom-modal .modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    border-top-left-radius: calc(0.3rem - 1px);
    border-top-right-radius: calc(0.3rem - 1px);
}
.notification-custom-modal .modal-body {
    position: relative;
    flex: 1 1 auto;
    padding: 1rem;
}
.notification-custom-modal .modal-footer {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    padding: 0.75rem;
    border-top: 1px solid #dee2e6;
    border-bottom-right-radius: calc(0.3rem - 1px);
    border-bottom-left-radius: calc(0.3rem - 1px);
}
.notification-custom-modal .modal-footer > * {
    margin: 0.25rem;
}
.notification-custom-modal .close {
    padding: 1rem;
    margin: -1rem -1rem -1rem auto;
    background-color: transparent;
    border: 0;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    cursor: pointer;
}
.notification-custom-modal .modal-sm {
    max-width: 300px;
}
</style>

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
						<div class="position-relative d-flex align-items-center">
							{{-- Send Firebase Button --}}
							<button type="button" 
								class="btn btn-sm btn-success mr-1 send-notification-btn" 
								data-notification-id="{{ $notification->id }}"
								data-notification-title="{{ $notification->title }}"
								data-cities-count="{{ $notification->cities->count() }}"
								title="Send via Firebase"
								@if($notification->cities->count() == 0) disabled @endif>
								<i class="gd-rocket"></i>
							</button>
							
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
								<option value="send">Send Selected via Firebase</option>
							</select>
						</div>
						<div class="form-group col-md-3">
							<button type="submit" class="btn btn-warning" id="bulkActionBtn">
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
	var currentNotificationId = null;
	var csrfToken = '{{ csrf_token() }}';
	var baseUrl = '{{ url("/notifications") }}';

	// Create modal backdrop
	var backdrop = document.createElement('div');
	backdrop.className = 'notification-modal-backdrop';
	backdrop.id = 'notificationModalBackdrop';
	document.body.appendChild(backdrop);

	// Create Send Confirmation Modal
	var sendModalHtml = `
		<div class="notification-custom-modal" id="sendNotificationModal">
			<div class="modal-dialog modal-dialog-centered" style="background-color:transparent;">
				<div class="modal-content">
					<div class="modal-header" style="background-color: #28a745; color: white;">
						<h5 class="modal-title">
							<i class="gd-rocket"></i> Send Firebase Notification
						</h5>
						<button type="button" class="close" onclick="closeModal('sendNotificationModal')" style="color: white; opacity: 1;">
							<span>&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<p class="mb-3">Are you sure you want to send this notification?</p>
						<div class="card">
							<div class="card-body p-3">
								<div class="row">
									<div class="col-3 text-muted">Title:</div>
									<div class="col-9"><strong id="modalNotificationTitle">-</strong></div>
								</div>
								<hr class="my-2">
								<div class="row">
									<div class="col-3 text-muted">Target:</div>
									<div class="col-9"><strong><span id="modalCitiesCount">0</span> city/cities</strong></div>
								</div>
							</div>
						</div>
						<div class="alert alert-warning mt-3 mb-0">
							<small><i class="gd-info-alt"></i> This will send a push notification to all mobile app users in the selected cities.</small>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" onclick="closeModal('sendNotificationModal')">Cancel</button>
						<button type="button" class="btn btn-success" id="confirmSendBtn">
							<i class="gd-rocket"></i> Send Now
						</button>
					</div>
				</div>
			</div>
		</div>
	`;
	document.body.insertAdjacentHTML('beforeend', sendModalHtml);

	// Create Loading Modal
	var loadingModalHtml = `
		<div class="notification-custom-modal" id="loadingModal">
			<div class="modal-dialog modal-dialog-centered modal-sm" style="background-color:transparent;">
				<div class="modal-content">
					<div class="modal-body text-center py-4">
						<div class="spinner-border text-success mb-3" role="status" style="width: 3rem; height: 3rem;">
							<span class="sr-only">Loading...</span>
						</div>
						<h5 class="mb-1">Sending notification...</h5>
						<p class="text-muted mb-0 small">Please wait</p>
					</div>
				</div>
			</div>
		</div>
	`;
	document.body.insertAdjacentHTML('beforeend', loadingModalHtml);

	// Create Result Modal
	var resultModalHtml = `
		<div class="notification-custom-modal" id="resultModal">
			<div class="modal-dialog modal-dialog-centered" style="background-color:transparent;">
				<div class="modal-content">
					<div class="modal-header" id="resultModalHeader" style="background-color: #28a745; color: white;">
						<h5 class="modal-title" id="resultModalTitle">Result</h5>
						<button type="button" class="close" onclick="closeModal('resultModal')" style="color: white; opacity: 1;">
							<span>&times;</span>
						</button>
					</div>
					<div class="modal-body" id="resultModalBody"></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" onclick="closeModal('resultModal')">OK</button>
					</div>
				</div>
			</div>
		</div>
	`;
	document.body.insertAdjacentHTML('beforeend', resultModalHtml);

	// Modal functions
	window.openModal = function(modalId) {
		document.getElementById('notificationModalBackdrop').classList.add('show');
		document.getElementById(modalId).classList.add('show');
		document.body.style.overflow = 'hidden';
	};

	window.closeModal = function(modalId) {
		document.getElementById(modalId).classList.remove('show');
		document.getElementById('notificationModalBackdrop').classList.remove('show');
		document.body.style.overflow = '';
	};

	// Close modal when clicking backdrop
	backdrop.addEventListener('click', function() {
		document.querySelectorAll('.notification-custom-modal.show').forEach(function(modal) {
			modal.classList.remove('show');
		});
		backdrop.classList.remove('show');
		document.body.style.overflow = '';
	});

	// Select all checkbox functionality
	var selectAllCheckbox = document.getElementById('selectAll');
	if (selectAllCheckbox) {
		selectAllCheckbox.addEventListener('change', function() {
			var checkboxes = document.querySelectorAll('.notification-checkbox');
			checkboxes.forEach(function(checkbox) {
				checkbox.checked = selectAllCheckbox.checked;
			});
		});
	}

	// Bulk action form submission
	var bulkActionBtn = document.getElementById('bulkActionBtn');
	if (bulkActionBtn) {
		bulkActionBtn.addEventListener('click', function(e) {
			e.preventDefault();
			
			var action = document.getElementById('bulk_action').value;
			var selectedCheckboxes = document.querySelectorAll('.notification-checkbox:checked');
			
			if (!action) {
				alert('Please select an action.');
				return;
			}
			
			if (selectedCheckboxes.length === 0) {
				alert('Please select at least one notification.');
				return;
			}

			var confirmMsg = action === 'delete' 
				? 'Are you sure you want to delete ' + selectedCheckboxes.length + ' notification(s)? This action cannot be undone.'
				: 'Are you sure you want to send ' + selectedCheckboxes.length + ' notification(s) via Firebase?';

			if (!confirm(confirmMsg)) {
				return;
			}

			var form = document.getElementById('bulkActionForm');
			selectedCheckboxes.forEach(function(checkbox) {
				var input = document.createElement('input');
				input.type = 'hidden';
				input.name = 'notification_ids[]';
				input.value = checkbox.value;
				form.appendChild(input);
			});

			form.submit();
		});
	}

	// Send notification button click
	var sendButtons = document.querySelectorAll('.send-notification-btn');
	sendButtons.forEach(function(button) {
		button.addEventListener('click', function() {
			var notificationId = this.getAttribute('data-notification-id');
			var notificationTitle = this.getAttribute('data-notification-title');
			var citiesCount = this.getAttribute('data-cities-count');

			if (parseInt(citiesCount) === 0) {
				alert('This notification has no cities assigned. Please edit the notification and assign cities first.');
				return;
			}

			currentNotificationId = notificationId;
			document.getElementById('modalNotificationTitle').textContent = notificationTitle;
			document.getElementById('modalCitiesCount').textContent = citiesCount;

			openModal('sendNotificationModal');
		});
	});

	// Confirm send button click
	var confirmSendBtn = document.getElementById('confirmSendBtn');
	if (confirmSendBtn) {
		confirmSendBtn.addEventListener('click', function() {
			if (!currentNotificationId) return;

			// Hide confirmation modal and show loading
			closeModal('sendNotificationModal');
			
			setTimeout(function() {
				openModal('loadingModal');
				
				// Send AJAX request
				var xhr = new XMLHttpRequest();
				xhr.open('POST', baseUrl + '/' + currentNotificationId + '/send-ajax', true);
				xhr.setRequestHeader('Content-Type', 'application/json');
				xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
				xhr.setRequestHeader('Accept', 'application/json');
				
				xhr.onload = function() {
					closeModal('loadingModal');
					
					var data;
					try {
						data = JSON.parse(xhr.responseText);
					} catch (e) {
						data = { success: false, message: 'Invalid response from server' };
					}
					
					setTimeout(function() {
						showResultModal(data);
					}, 200);
				};
				
				xhr.onerror = function() {
					closeModal('loadingModal');
					
					setTimeout(function() {
						showResultModal({ success: false, message: 'Network error occurred. Please try again.' });
					}, 200);
				};
				
				xhr.send();
			}, 200);
		});
	}

	function showResultModal(data) {
		var header = document.getElementById('resultModalHeader');
		var title = document.getElementById('resultModalTitle');
		var body = document.getElementById('resultModalBody');
		var html = '';

		if (data.success) {
			header.style.backgroundColor = '#28a745';
			title.innerHTML = '<i class="gd-check"></i> Success';
			
			html = '<div class="alert alert-success">' + escapeHtml(data.message || 'Notification sent successfully') + '</div>';

			if (data.summary) {
				html += '<div class="row text-center mb-3">';
				html += '<div class="col-4"><h4 class="text-primary mb-0">' + data.summary.total + '</h4><small class="text-muted">Total</small></div>';
				html += '<div class="col-4"><h4 class="text-success mb-0">' + data.summary.success + '</h4><small class="text-muted">Success</small></div>';
				html += '<div class="col-4"><h4 class="text-danger mb-0">' + data.summary.failed + '</h4><small class="text-muted">Failed</small></div>';
				html += '</div>';
			}

			if (data.results && data.results.success && data.results.success.length > 0) {
				html += '<p class="mb-2"><strong>Sent to:</strong></p><div class="mb-3">';
				data.results.success.forEach(function(city) {
					html += '<span class="badge badge-success mr-1 mb-1">' + escapeHtml(city.name) + '</span>';
				});
				html += '</div>';
			}

			if (data.results && data.results.failed && data.results.failed.length > 0) {
				html += '<p class="mb-2 text-danger"><strong>Failed:</strong></p><div>';
				data.results.failed.forEach(function(city) {
					html += '<span class="badge badge-danger mr-1 mb-1" title="' + escapeHtml(city.error || 'Unknown error') + '">' + escapeHtml(city.name) + '</span>';
				});
				html += '</div>';
			}
		} else {
			header.style.backgroundColor = '#dc3545';
			title.innerHTML = '<i class="gd-close"></i> Failed';
			html = '<div class="alert alert-danger">' + escapeHtml(data.message || 'Failed to send notification') + '</div>';
		}

		body.innerHTML = html;
		openModal('resultModal');
	}

	function escapeHtml(text) {
		if (!text) return '';
		var div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}
});
</script>

@endsection