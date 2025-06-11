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
					@endif
					<div>
						<strong>Related Service:</strong>
						<p class="mb-0">{{ $notification->service->name }}</p>
						<small class="text-muted">{{ $notification->service->city->name ?? 'No City' }}</small>
					</div>
				</div>
				@endif

				@if($notification->news)
				<div class="d-flex align-items-center mb-3">
					@if($notification->news->image)
						<img src="{{ asset('storage/' . $notification->news->image->path) }}" alt="{{ $notification->news->name }}" class="rounded mr-3" width="50" height="50">
					@endif
					<div>
						<strong>Related News:</strong>
						<p class="mb-0">{{ $notification->news->name }}</p>
						<small class="text-muted">{{ Str::limit($notification->news->text, 100) }}</small>
					</div>
				</div>
				@endif
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

	</div>
</div>
@endsection