@extends('layouts.grain')

@section('title', 'Notification Details')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">
	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('notification.index') }}">Notifications</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ $notification->title }}</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
			<div class="d-flex align-items-center">
				@if($notification->image)
					<img src="{{ asset('storage/' . $notification->image->path) }}" alt="{{ $notification->title }}" class="rounded mr-3" width="60" height="60">
				@endif
				<div>
					<h3 class="mb-0">{{ $notification->title }}</h3>
					<small class="text-muted">Created {{ $notification->created_at ? $notification->created_at->format('M d, Y \a\t g:i A') : 'Unknown' }}</small>
				</div>
			</div>
			<div>
				<a href="{{ route('notification.edit', $notification) }}" class="btn btn-primary">
					<i class="gd-pencil"></i> Edit Notification
				</a>
			</div>
		</div>

		<!-- Notification Content -->
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">Notification Content</h5>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-12">
						<strong>Title:</strong>
						<p class="mb-3">{{ $notification->title }}</p>
						
						<strong>Body:</strong>
						<p class="mb-3">{{ $notification->body }}</p>
					</div>
				</div>
			</div>
		</div>

		<!-- Assigned Cities -->
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">Assigned Cities</h5>
			</div>
			<div class="card-body">
				@if($notification->cities->count() > 0)
					<div class="row">
						@foreach($notification->cities as $city)
							<div class="col-md-3 mb-3">
								<div class="d-flex align-items-center p-2 border rounded">
									@if($city->image)
										<img src="{{ asset('storage/' . $city->image->path) }}" alt="{{ $city->name }}" class="rounded mr-2" width="40" height="40">
									@else
										<span class="avatar-placeholder bg-primary text-white rounded d-inline-flex align-items-center justify-content-center mr-2" style="width: 40px; height: 40px;">
											<i class="gd-map-alt"></i>
										</span>
									@endif
									<div>
										<strong>{{ $city->name }}</strong>
									</div>
								</div>
							</div>
						@endforeach
					</div>
				@else
					<p class="text-muted">No cities assigned to this notification.</p>
				@endif
			</div>
		</div>

		<!-- Related Content -->
		@if($notification->service || $notification->news)
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">Related Content</h5>
			</div>
			<div class="card-body">
				@if($notification->service)
				<div class="d-flex align-items-center mb-3">
					@if($notification->service->image)
						<img src="{{ asset('storage/' . $notification->service->image->path) }}" alt="{{ $notification->service->name }}" class="rounded mr-3" width="50" height="50">
					@else
						<span class="avatar-placeholder bg-info text-white rounded d-inline-flex align-items-center justify-content-center mr-3" style="width: 50px; height: 50px;">
							<i class="gd-layers"></i>
						</span>
					@endif
					<div>
						<strong>Related Service:</strong>
						<p class="mb-0">{{ $notification->service->name }}</p>
						<small class="text-muted">{{ $notification->service->city->name ?? 'No City' }}</small>
						@if($notification->service->brief_description)
							<p class="text-muted mt-1 mb-0">{{ Str::limit($notification->service->brief_description, 100) }}</p>
						@endif
					</div>
				</div>
				@endif

				@if($notification->news)
				<div class="d-flex align-items-center mb-3">
					@if($notification->news->image)
						<img src="{{ asset('storage/' . $notification->news->image->path) }}" alt="{{ $notification->news->name }}" class="rounded mr-3" width="50" height="50">
					@else
						<span class="avatar-placeholder bg-success text-white rounded d-inline-flex align-items-center justify-content-center mr-3" style="width: 50px; height: 50px;">
							<i class="gd-file"></i>
						</span>
					@endif
					<div>
						<strong>Related News:</strong>
						<p class="mb-0">{{ $notification->news->name }}</p>
						<small class="text-muted">{{ $notification->news->city->name ?? 'No City' }}</small>
						@if($notification->news->description)
							<p class="text-muted mt-1 mb-0">{{ Str::limit($notification->news->description, 100) }}</p>
						@endif
					</div>
				</div>
				@endif
			</div>
		</div>
		@else
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">Related Content</h5>
			</div>
			<div class="card-body">
				<p class="text-muted">No related content assigned to this notification.</p>
			</div>
		</div>
		@endif

		<!-- Notification Image -->
		@if($notification->image)
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0">Notification Image</h5>
			</div>
			<div class="card-body text-center">
				<img src="{{ asset('storage/' . $notification->image->path) }}" alt="{{ $notification->title }}" class="img-fluid" style="max-width: 400px;">
			</div>
		</div>
		@endif

		<!-- Notification Stats -->
		<div class="card mt-4">
			<div class="card-header">
				<h5 class="mb-0">Notification Statistics</h5>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-3">
						<div class="text-center">
							<h4 class="text-primary">{{ $notification->cities->count() }}</h4>
							<p class="text-muted">Cities Assigned</p>
						</div>
					</div>
					<div class="col-md-3">
						<div class="text-center">
							<h4 class="text-info">{{ $notification->service ? 1 : 0 }}</h4>
							<p class="text-muted">Related Services</p>
						</div>
					</div>
					<div class="col-md-3">
						<div class="text-center">
							<h4 class="text-success">{{ $notification->news ? 1 : 0 }}</h4>
							<p class="text-muted">Related News</p>
						</div>
					</div>
					<div class="col-md-3">
						<div class="text-center">
							<h4 class="text-secondary">{{ $notification->image ? 1 : 0 }}</h4>
							<p class="text-muted">Images Attached</p>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>
@endsection