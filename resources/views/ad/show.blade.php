@extends('layouts.grain')

@section('title', 'Ad Details')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">
	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('ad.index') }}">Ads</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ $ad->name }}</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
			<div class="d-flex align-items-center">
				@if($ad->image)
					<img src="{{ asset('storage/' . $ad->image->path) }}" alt="{{ $ad->name }}" class="rounded mr-3" width="80" height="80">
				@endif
				<div>
					<h3 class="mb-0">{{ $ad->name }}</h3>
					<small class="text-muted">Created {{ $ad->created_at ? $ad->created_at->format('M d, Y') : 'Unknown' }}</small>
				</div>
			</div>
			<div>
				<a href="{{ route('ad.edit', $ad) }}" class="btn btn-primary">
					<i class="gd-pencil"></i> Edit Ad
				</a>
			</div>
		</div>

		<!-- Ad Details -->
		<div class="row">
			<div class="col-md-8">
				<div class="card">
					<div class="card-header">
						<h5 class="mb-0">Ad Information</h5>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-6">
								<div class="mb-3">
									<strong>Ad Name:</strong>
									<p class="mb-0">{{ $ad->name }}</p>
								</div>
								
								<div class="mb-3">
									<strong>Location:</strong>
									<p class="mb-0">
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
									</p>
								</div>
								
								<div class="mb-3">
									<strong>City:</strong>
									<p class="mb-0">
										<span class="badge badge-info">{{ $ad->city->name }}</span>
									</p>
								</div>
								
								<div class="mb-0">
									<strong>Created:</strong>
									<p class="mb-0">{{ $ad->created_at ? $ad->created_at->format('M d, Y \a\t g:i A') : 'Unknown' }}</p>
									<small class="text-muted">{{ $ad->created_at ? $ad->created_at->diffForHumans() : '' }}</small>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-4">
				@if($ad->image)
				<!-- Ad Image -->
				<div class="card mb-3">
					<div class="card-header">
						<h5 class="mb-0">Ad Image</h5>
					</div>
					<div class="card-body text-center">
						<img src="{{ asset('storage/' . $ad->image->path) }}" alt="{{ $ad->name }}" class="img-fluid rounded" style="max-width: 100%;">
					</div>
				</div>
				@endif

				<!-- Quick Actions -->
				<div class="card">
					<div class="card-header">
						<h5 class="mb-0">Quick Actions</h5>
					</div>
					<div class="card-body">
						<div class="d-grid gap-2">
							<a href="{{ route('ad.edit', $ad) }}" class="btn btn-primary btn-sm">
								<i class="gd-pencil"></i> Edit Ad
							</a>
							
							<a href="{{ route('ad.index') }}" class="btn btn-secondary btn-sm">
								<i class="gd-arrow-left"></i> Back to Ads
							</a>
							
							<hr>
							
							<button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Delete this ad? This action cannot be undone.')){document.getElementById('delete-form').submit();}">
								<i class="gd-trash"></i> Delete Ad
							</button>
							
							<form id="delete-form" action="{{ route('ad.destroy', $ad) }}" method="POST" style="display: none;">
								<input type="hidden" name="_method" value="DELETE">
								@csrf
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Navigation -->
		@php
			$prevAd = \App\Models\Ad::where('id', '<', $ad->id)->orderBy('id', 'desc')->first();
			$nextAd = \App\Models\Ad::where('id', '>', $ad->id)->orderBy('id', 'asc')->first();
		@endphp
		
		@if($prevAd || $nextAd)
		<div class="row mt-4">
			<div class="col-12">
				<div class="d-flex justify-content-between">
					<div>
						@if($prevAd)
							<a href="{{ route('ad.show', $prevAd) }}" class="btn btn-outline-secondary">
								<i class="gd-arrow-left"></i> Previous Ad
							</a>
						@endif
					</div>
					<div>
						@if($nextAd)
							<a href="{{ route('ad.show', $nextAd) }}" class="btn btn-outline-secondary">
								Next Ad <i class="gd-arrow-right"></i>
							</a>
						@endif
					</div>
				</div>
			</div>
		</div>
		@endif

	</div>
</div>
@endsection