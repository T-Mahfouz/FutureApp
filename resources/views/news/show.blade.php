@extends('layouts.grain')

@section('title', 'News Details')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">
	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('news.index') }}">News</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ $news->name }}</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
			<div class="d-flex align-items-center">
				@if($news->image)
					<img src="{{ asset('storage/' . $news->image->path) }}" alt="{{ $news->name }}" class="rounded mr-3" width="60" height="60">
				@endif
				<div>
					<h3 class="mb-0">{{ $news->name }}</h3>
					<small class="text-muted">
						Created {{ $news->created_at ? $news->created_at->format('M d, Y') : 'Unknown' }}
						@if($news->city)
							• City: <strong>{{ $news->city->name }}</strong>
						@endif
					</small>
				</div>
			</div>
			<div>
				<a href="{{ route('news.edit', $news) }}" class="btn btn-primary">
					<i class="gd-pencil"></i> Edit News
				</a>
			</div>
		</div>

		<!-- News Content -->
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">News Description</h5>
			</div>
			<div class="card-body">
				<div class="mb-3">
					{!! nl2br(e($news->description)) !!}
				</div>
				@if($news->city)
				<div class="mt-3 pt-3 border-top">
					<strong>City:</strong> 
					<span class="badge badge-primary">{{ $news->city->name }}</span>
				</div>
				@endif
			</div>
		</div>

		<!-- Main Image -->
		@if($news->image)
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">Main Image</h5>
			</div>
			<div class="card-body">
				<img src="{{ asset('storage/' . $news->image->path) }}" alt="{{ $news->name }}" class="img-fluid rounded" style="max-height: 400px;">
			</div>
		</div>
		@endif

		<!-- Additional Images -->
		@if($news->images->count() > 0)
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">Additional Images ({{ $news->images->count() }})</h5>
			</div>
			<div class="card-body">
				<div class="row">
					@foreach($news->images as $image)
					<div class="col-md-4 mb-3">
						<img src="{{ asset('storage/' . $image->path) }}" alt="Additional Image" class="img-fluid rounded" style="height: 200px; width: 100%; object-fit: cover;">
					</div>
					@endforeach
				</div>
			</div>
		</div>
		@endif

		<!-- Related Notifications -->
		@if($news->notifications->count() > 0)
		<div class="card mb-4">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0">Related Notifications ({{ $news->notifications->count() }})</h5>
			</div>
			<div class="card-body">
				@foreach($news->notifications as $notification)
				<div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
					@if($notification->image)
						<img src="{{ asset('storage/' . $notification->image->path) }}" alt="{{ $notification->title }}" class="rounded mr-3" width="40" height="40">
					@else
						<span class="avatar-placeholder mr-3">{{ substr($notification->title, 0, 1) }}</span>
					@endif
					<div class="flex-grow-1">
						<strong>{{ $notification->title }}</strong>
						<br><small class="text-muted">{{ Str::limit($notification->body, 80) }}</small>
					</div>
					<div class="text-right">
						<small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
					</div>
				</div>
				@endforeach
			</div>
		</div>
		@endif

		<!-- News Statistics -->
		<div class="row">
			<div class="col-md-6">
				<div class="card bg-info text-white">
					<div class="card-body text-center">
						<h4>{{ $news->notifications()->count() }}</h4>
						<small>Related Notifications</small>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="card bg-success text-white">
					<div class="card-body text-center">
						<h4>{{ $news->images()->count() }}</h4>
						<small>Additional Images</small>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>
@endsection