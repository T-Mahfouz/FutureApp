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
					<br>
					<!-- NEW: Status badge -->
					@if($ad->expiration_date && $ad->expiration_date->isPast())
						<span class="badge badge-danger">Expired</span>
					@else
						<span class="badge badge-success">Active</span>
					@endif
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
												'service_profile' => 'Service Profile',
												'all_locations' => 'All Locations' // NEW: Add all_locations label
											];
											$locationColors = [
												'home' => 'primary',
												'category_profile' => 'success',
												'service_profile' => 'info',
												'all_locations' => 'warning' // NEW: Add color for all_locations
											];
										@endphp
										<span class="badge badge-{{ $locationColors[$ad->location] ?? 'secondary' }}">
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

								<!-- NEW: Link information -->
								<div class="mb-3">
									<strong>Link:</strong>
									<p class="mb-0">
										@if($ad->link)
											<a href="{{ $ad->link }}" target="_blank" class="btn btn-sm btn-outline-primary">
												<i class="gd-link"></i> {{ Str::limit($ad->link, 40) }}
											</a>
										@else
											<span class="text-muted font-italic">No link provided</span>
										@endif
									</p>
								</div>

								<!-- NEW: Expiration Date information -->
								<div class="mb-3">
									<strong>Expiration Date:</strong>
									<p class="mb-0">
										@if($ad->expiration_date)
											@if($ad->expiration_date->isPast())
												<span class="badge badge-danger">Expired</span>
												<br><small class="text-muted">
													Expired on {{ $ad->expiration_date->format('M d, Y \a\t g:i A') }}<br>
													({{ $ad->expiration_date->diffForHumans() }})
												</small>
											@else
												<span class="badge badge-warning">Will Expire</span>
												<br><small class="text-muted">
													{{ $ad->expiration_date->format('M d, Y \a\t g:i A') }}<br>
													({{ $ad->expiration_date->diffForHumans() }})
												</small>
											@endif
										@else
											<span class="text-muted font-italic">No expiration date set</span>
											<br><small class="text-muted">This ad will run indefinitely</small>
										@endif
									</p>
								</div>

								<!-- NEW: Status information -->
								<div class="mb-3">
									<strong>Status:</strong>
									<p class="mb-0">
										@if($ad->expiration_date && $ad->expiration_date->isPast())
											<span class="badge badge-danger">Expired</span>
											<br><small class="text-muted">This ad is no longer active</small>
										@else
											<span class="badge badge-success">Active</span>
											<br><small class="text-muted">This ad is currently active and visible</small>
										@endif
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
						<!-- NEW: Show link overlay if ad has link -->
						@if($ad->link)
							<div class="mt-2">
								<small class="text-muted">This ad links to:</small><br>
								<a href="{{ $ad->link }}" target="_blank" class="btn btn-sm btn-primary">
									<i class="gd-link"></i> Visit Link
								</a>
							</div>
						@endif
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
							
							<!-- NEW: Test link button -->
							@if($ad->link)
							<a href="{{ $ad->link }}" target="_blank" class="btn btn-outline-info btn-sm">
								<i class="gd-link"></i> Test Link
							</a>
							@endif
							
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

				<!-- NEW: Expiration Info Card -->
				@if($ad->expiration_date)
				<div class="card mt-3">
					<div class="card-header">
						<h6 class="mb-0">
							<i class="gd-time mr-2"></i>Expiration Details
						</h6>
					</div>
					<div class="card-body">
						<div class="text-center">
							@if($ad->expiration_date->isPast())
								<i class="gd-alarm text-danger" style="font-size: 32px;"></i>
								<h6 class="text-danger mt-2 mb-1">Expired</h6>
								<p class="text-muted mb-2">
									<strong>{{ $ad->expiration_date->format('M d, Y') }}</strong><br>
									<small>{{ $ad->expiration_date->format('g:i A') }}</small>
								</p>
								<span class="badge badge-danger">{{ $ad->expiration_date->diffForHumans() }}</span>
							@else
								<i class="gd-time text-warning" style="font-size: 32px;"></i>
								<h6 class="text-warning mt-2 mb-1">Will Expire</h6>
								<p class="text-muted mb-2">
									<strong>{{ $ad->expiration_date->format('M d, Y') }}</strong><br>
									<small>{{ $ad->expiration_date->format('g:i A') }}</small>
								</p>
								<span class="badge badge-warning">{{ $ad->expiration_date->diffForHumans() }}</span>
							@endif
						</div>
					</div>
				</div>
				@else
				<div class="card mt-3">
					<div class="card-header">
						<h6 class="mb-0">
							<i class="gd-infinite mr-2"></i>No Expiration
						</h6>
					</div>
					<div class="card-body">
						<div class="text-center">
							<i class="gd-infinite text-primary" style="font-size: 32px;"></i>
							<h6 class="text-primary mt-2 mb-1">Permanent Ad</h6>
							<p class="text-muted mb-0">
								This ad will run indefinitely until manually disabled or deleted.
							</p>
						</div>
					</div>
				</div>
				@endif
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