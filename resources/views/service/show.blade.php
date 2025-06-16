@extends('layouts.grain')

@section('title', 'Service Details')

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
				<li class="breadcrumb-item active" aria-current="page">{{ $service->name }}</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
			<div class="d-flex align-items-center">
				@if($service->image)
					<img src="{{ asset('storage/' . $service->image->path) }}" alt="{{ $service->name }}" class="rounded mr-3" width="80" height="80">
				@endif
				<div>
					<h3 class="mb-0">{{ $service->name }}</h3>
					<div class="d-flex align-items-center mt-1">
						<span class="badge badge-{{ $service->valid ? 'success' : 'danger' }} mr-2">
							{{ $service->valid ? 'Active' : 'Inactive' }}
						</span>
						@if($service->is_request)
							<span class="badge badge-info mr-2">User Request</span>
						@endif
						@if($service->is_add)
							<span class="badge badge-info mr-2">Advertisement</span>
						@endif
						@if($service->parent_id)
							<span class="badge badge-secondary">Sub-service</span>
						@endif
					</div>
					<small class="text-muted">Created {{ $service->created_at ? $service->created_at->format('M d, Y') : 'Unknown' }}</small>
				</div>
			</div>
			<div>
				<a href="{{ route('service.edit', $service) }}" class="btn btn-primary">
					<i class="gd-pencil"></i> Edit Service
				</a>
			</div>
		</div>

		<!-- Service Statistics -->
		<div class="row mb-4">
			<div class="col-md-3">
				<div class="card bg-info text-white">
					<div class="card-body text-center">
						<h4>{{ $service->rates()->count() }}</h4>
						<small>Total Ratings</small>
						@if($service->rates()->count() > 0)
							<div class="mt-1">
								<small>Avg: {{ number_format($service->averageRating(), 1) }} ★</small>
							</div>
						@endif
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card bg-success text-white">
					<div class="card-body text-center">
						<h4>{{ $service->favorites()->count() }}</h4>
						<small>Favorites</small>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card bg-warning text-white">
					<div class="card-body text-center">
						<h4>{{ $service->phones()->count() }}</h4>
						<small>Phone Numbers</small>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card bg-secondary text-white">
					<div class="card-body text-center">
						<h4>{{ $service->images()->count() + ($service->image ? 1 : 0) }}</h4>
						<small>Total Images</small>
					</div>
				</div>
			</div>
		</div>

		<!-- Basic Information -->
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">Basic Information</h5>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-6">
						<strong>City:</strong>
						<p class="text-muted">{{ $service->city->name ?? 'N/A' }}</p>
					</div>
					<div class="col-md-6">
						<strong>Display Order:</strong>
						<p class="text-muted">{{ $service->arrangement_order ?? 'N/A' }}</p>
					</div>
				</div>

				@if($service->brief_description)
				<div class="row">
					<div class="col-12">
						<strong>Brief Description:</strong>
						<p class="text-muted">{{ $service->brief_description }}</p>
					</div>
				</div>
				@endif

				@if($service->description)
				<div class="row">
					<div class="col-12">
						<strong>Full Description:</strong>
						<div class="text-muted" style="white-space: pre-wrap;">{{ $service->description }}</div>
					</div>
				</div>
				@endif
			</div>
		</div>

		@if($service->is_request)
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="mb-0">Request Information</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<strong>Requested By:</strong>
							<p class="text-muted">
								@if($service->user)
									{{ $service->user->name }} ({{ $service->user->email }})
								@else
									Unknown User
								@endif
							</p>
						</div>
						<div class="col-md-6">
							<strong>Request Date:</strong>
							<p class="text-muted">
								{{ $service->requested_at ? $service->requested_at->format('M d, Y \a\t g:i A') : ($service->created_at ? $service->created_at->format('M d, Y \a\t g:i A') : 'N/A') }}
								<br><small>{{ $service->requested_at ? $service->requested_at->diffForHumans() : ($service->created_at ? $service->created_at->diffForHumans() : '') }}</small>
							</p>
						</div>
					</div>

					@if($service->isApproved())
					<div class="row">
						<div class="col-md-6">
							<strong>Approved By:</strong>
							<p class="text-muted">
								{{ $service->approvedBy->name ?? 'Unknown Admin' }}
							</p>
						</div>
						<div class="col-md-6">
							<strong>Approval Date:</strong>
							<p class="text-muted">
								{{ $service->approved_at ? $service->approved_at->format('M d, Y \a\t g:i A') : 'N/A' }}
								<br><small>{{ $service->approved_at ? $service->approved_at->diffForHumans() : '' }}</small>
							</p>
						</div>
					</div>
					@endif

					@if($service->isRejected())
					<div class="row">
						<div class="col-md-6">
							<strong>Rejected By:</strong>
							<p class="text-muted">
								{{ $service->rejectedBy->name ?? 'Unknown Admin' }}
							</p>
						</div>
						<div class="col-md-6">
							<strong>Rejection Date:</strong>
							<p class="text-muted">
								{{ $service->rejected_at ? $service->rejected_at->format('M d, Y \a\t g:i A') : 'N/A' }}
								<br><small>{{ $service->rejected_at ? $service->rejected_at->diffForHumans() : '' }}</small>
							</p>
						</div>
					</div>
					@if($service->rejection_reason)
					<div class="row">
						<div class="col-12">
							<strong>Rejection Reason:</strong>
							<div class="alert alert-danger mt-2">
								{{ $service->rejection_reason }}
							</div>
						</div>
					</div>
					@endif
					@endif

					<!-- NEW: Action buttons for pending requests -->
					@if($service->isPending())
					<div class="row mt-3">
						<div class="col-12">
							<div class="alert alert-warning">
								<strong>This service is pending approval.</strong>
							</div>
							<div class="d-flex">
								<button type="button" class="btn btn-success mr-2" onclick="approveService({{ $service->id }})">
									<i class="gd-check"></i> Approve Request
								</button>
								<button type="button" class="btn btn-danger" onclick="rejectService({{ $service->id }})">
									<i class="gd-close"></i> Reject Request
								</button>
							</div>
						</div>
					</div>
					@endif
				</div>
			</div>
		@endif

		<!-- Contact Information -->
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">Contact Information</h5>
			</div>
			<div class="card-body">
				<div class="row">
					@if($service->phones && $service->phones->count() > 0)
                    <div class="col-md-6 mb-3">
                        <strong>Primary Phone:</strong>
                        <p class="text-muted">{{ $service->phones->first()->phone }}</p>
                    </div>
                    @endif

					@if($service->whatsapp)
					<div class="col-md-6 mb-3">
						<strong>WhatsApp:</strong>
						<p class="text-muted">{{ $service->whatsapp }}</p>
					</div>
					@endif

					@if($service->address)
					<div class="col-12 mb-3">
						<strong>Address:</strong>
						<p class="text-muted">{{ $service->address }}</p>
					</div>
					@endif

					@if($service->lat && $service->lon)
					<div class="col-md-6 mb-3">
						<strong>Coordinates:</strong>
						<p class="text-muted">{{ $service->lat }}, {{ $service->lon }}</p>
					</div>
					@endif
				</div>

				<!-- All Phone Numbers -->
                @if($service->phones && $service->phones->count() > 1)
                <div class="row">
                    <div class="col-12">
                        <strong>Additional Phone Numbers:</strong>
                        <ul class="list-unstyled mt-2">
                            @foreach($service->phones->skip(1) as $phone)
                                <li class="text-muted">{{ $phone->phone }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
			</div>
		</div>

		<!-- Social Media and Links -->
		@if($service->website || $service->facebook || $service->instagram || $service->youtube || $service->telegram || $service->video_link)
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">Social Media and Links</h5>
			</div>
			<div class="card-body">
				<div class="row">
					@if($service->website)
					<div class="col-md-6 mb-3">
						<strong>Website:</strong>
						<p><a href="{{ $service->website }}" target="_blank" class="text-primary">{{ $service->website }}</a></p>
					</div>
					@endif

					@if($service->video_link)
					<div class="col-md-6 mb-3">
						<strong>Video Link:</strong>
						<p><a href="{{ $service->video_link }}" target="_blank" class="text-primary">{{ $service->video_link }}</a></p>
					</div>
					@endif

					@if($service->facebook)
					<div class="col-md-6 mb-3">
						<strong>Facebook:</strong>
						<p><a href="{{ $service->facebook }}" target="_blank" class="text-primary">{{ $service->facebook }}</a></p>
					</div>
					@endif

					@if($service->instagram)
					<div class="col-md-6 mb-3">
						<strong>Instagram:</strong>
						<p><a href="{{ $service->instagram }}" target="_blank" class="text-primary">{{ $service->instagram }}</a></p>
					</div>
					@endif

					@if($service->youtube)
					<div class="col-md-6 mb-3">
						<strong>YouTube:</strong>
						<p><a href="{{ $service->youtube }}" target="_blank" class="text-primary">{{ $service->youtube }}</a></p>
					</div>
					@endif

					@if($service->telegram)
					<div class="col-md-6 mb-3">
						<strong>Telegram:</strong>
						<p><a href="{{ $service->telegram }}" target="_blank" class="text-primary">{{ $service->telegram }}</a></p>
					</div>
					@endif
				</div>
			</div>
		</div>
		@endif

		<!-- Categories -->
		@if($service->categories && $service->categories->count() > 0)
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">Categories ({{ $service->categories->count() }})</h5>
			</div>
			<div class="card-body">
				<div class="d-flex flex-wrap">
					@foreach($service->categories as $category)
						<span class="badge badge-primary mr-2 mb-2">{{ $category->name }}</span>
					@endforeach
				</div>
			</div>
		</div>
		@endif

		<!-- Parent/Sub Services -->
		@if($service->parentService || $service->subServices->count() > 0)
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">Service Hierarchy</h5>
			</div>
			<div class="card-body">
				@if($service->parentService)
				<div class="mb-3">
					<strong>Parent Service:</strong>
					<p><a href="{{ route('service.show', $service->parentService) }}" class="text-primary">{{ $service->parentService->name }}</a></p>
				</div>
				@endif

				@if($service->subServices->count() > 0)
				<div>
					<strong>Sub-services ({{ $service->subServices->count() }}):</strong>
					<ul class="list-unstyled mt-2">
						@foreach($service->subServices as $subService)
							<li class="mb-1">
								<a href="{{ route('service.show', $subService) }}" class="text-primary">{{ $subService->name }}</a>
								<span class="badge badge-{{ $subService->valid ? 'success' : 'danger' }} ml-2">
									{{ $subService->valid ? 'Active' : 'Inactive' }}
								</span>
							</li>
						@endforeach
					</ul>
				</div>
				@endif
			</div>
		</div>
		@endif

		<!-- Images Gallery -->
		@if($service->images && $service->images->count() > 0)
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">Images Gallery ({{ $service->images->count() }})</h5>
			</div>
			<div class="card-body">
				<div class="row">
					@foreach($service->images as $image)
					<div class="col-md-3 col-sm-6 mb-3">
						<img src="{{ asset('storage/' . $image->path) }}" alt="Service Image" 
							 class="img-fluid img-thumbnail" style="height: 150px; object-fit: cover; width: 100%;">
					</div>
					@endforeach
				</div>
			</div>
		</div>
		@endif

		<!-- Recent Ratings -->
		@if($service->rates && $service->rates->count() > 0)
		<div class="card mb-4">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0">Recent Ratings ({{ $service->rates->count() }} total)</h5>
				<small class="text-muted">Average: {{ number_format($service->averageRating(), 1) }} ★</small>
			</div>
			<div class="card-body">
				@foreach($service->rates()->with('user')->latest()->take(10)->get() as $rate)
				<div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
					<div>
						<strong>{{ $rate->user->name ?? 'Anonymous' }}</strong>
						<small class="text-muted ml-2">{{ $rate->created_at->diffForHumans() }}</small>
					</div>
					<div class="text-warning">
						@for($i = 1; $i <= 5; $i++)
							{{ $i <= $rate->rate ? '★' : '☆' }}
						@endfor
						<span class="text-muted ml-1">({{ $rate->rate }}/5)</span>
					</div>
				</div>
				@endforeach
			</div>
		</div>
		@endif

		<!-- Recent Favorites -->
		@if($service->favorites && $service->favorites->count() > 0)
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0">Recent Favorites ({{ $service->favorites->count() }} total)</h5>
			</div>
			<div class="card-body">
				@foreach($service->favorites()->with('user')->latest()->take(5)->get() as $favorite)
				<div class="d-flex align-items-center justify-content-between mb-2">
					<strong>{{ $favorite->user->name ?? 'Anonymous' }}</strong>
					<small class="text-muted">{{ $favorite->created_at->diffForHumans() }}</small>
				</div>
				@endforeach
			</div>
		</div>
		@endif

	</div>
</div>
@endsection


<script>
function approveService(serviceId) {
    if(confirm('Are you sure you want to approve this service request?')) {

		let url = `{{ url('') }}/services/${serviceId}/approve`;

		$.ajax({
			url: url,
			type: 'POST',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			contentType: 'application/json',
			dataType: 'json',
			success: function(data) {
				if(data.success) {
					location.reload();
				} else {
					alert(data.message || 'Error approving service');
				}
			},
			error: function(xhr, status, error) {
				alert('Error approving service');
			}
		});
    }
}

function rejectService(serviceId) {
    const reason = prompt('Please provide a reason for rejection:');

	let url = `{{ url('') }}/services/${serviceId}/reject`;
	
    if(reason && reason.trim()) {
        $.ajax({
			url: url,
			type: 'POST',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			contentType: 'application/json',
			dataType: 'json',
			data: JSON.stringify({
				rejection_reason: reason.trim()
			}),
			success: function(data) {
				if(data.success) {
					location.reload();
				} else {
					alert(data.message || 'Error rejecting service');
				}
			},
			error: function(xhr, status, error) {
				alert('Error rejecting service');
			}
		});
    }
}
</script>