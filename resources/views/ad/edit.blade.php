@extends('layouts.grain')

@section('title', 'Dashboard')

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
				<li class="breadcrumb-item active" aria-current="page">{{ $ad->id ? 'Edit' : 'Create New' }} Ad</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">{{ $ad->id ? 'Edit' : 'Create New' }} Ad</div>
		</div>

		<!-- Form -->
		<div>
			<form method="post" action="{{ $ad->id ? route('ad.update', $ad) : route('ad.store') }}" enctype="multipart/form-data">
                @if($ad->id)
				<input type="hidden" name="_method" value="patch">
                @endif
				@csrf
				
				<div class="row">
					<!-- Main Form -->
					<div class="col-lg-8">
						<div class="card">
							<div class="card-header">
								<h6 class="mb-0">Ad Information</h6>
							</div>
							<div class="card-body">
								<div class="form-row">
									<div class="form-group col-12">
										<label for="name">Ad Name <span class="text-danger">*</span></label>
										<input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name', $ad->name) }}" id="name" name="name" placeholder="Enter ad name" required>
										@if($errors->has('name'))
											<div class="invalid-feedback">{{ $errors->first('name') }}</div>
										@endif
									</div>
								</div>

								<div class="form-row">
									<div class="form-group col-12 col-md-6">
										<label for="location">Location <span class="text-danger">*</span></label>
										<select class="form-control{{ $errors->has('location') ? ' is-invalid' : '' }}" id="location" name="location" required>
											<option value="">Select Location</option>
											<option value="home" {{ old('location', $ad->location) == 'home' ? 'selected' : '' }}>Home</option>
											<option value="category_profile" {{ old('location', $ad->location) == 'category_profile' ? 'selected' : '' }}>Category Profile</option>
											<option value="service_profile" {{ old('location', $ad->location) == 'service_profile' ? 'selected' : '' }}>Service Profile</option>
											<option value="all_locations" {{ old('location', $ad->location) == 'all_locations' ? 'selected' : '' }}>All Locations</option> <!-- NEW: Add all_locations option -->
										</select>
										@if($errors->has('location'))
											<div class="invalid-feedback">{{ $errors->first('location') }}</div>
										@endif
									</div>
									<div class="form-group col-12 col-md-6">
										<label for="city_id">City <span class="text-danger">*</span></label>
										<select class="form-control{{ $errors->has('city_id') ? ' is-invalid' : '' }}" id="city_id" name="city_id" required>
											<option value="">Select City</option>
											@foreach($cities as $city)
												<option value="{{ $city->id }}" {{ old('city_id', $ad->city_id) == $city->id ? 'selected' : '' }}>
													{{ $city->name }}
												</option>
											@endforeach
										</select>
										@if($errors->has('city_id'))
											<div class="invalid-feedback">{{ $errors->first('city_id') }}</div>
										@endif
									</div>
								</div>

								<!-- NEW: Link field -->
								<div class="form-row">
									<div class="form-group col-12">
										<label for="link">Link URL</label>
										<input type="url" class="form-control{{ $errors->has('link') ? ' is-invalid' : '' }}" value="{{ old('link', $ad->link) }}" id="link" name="link" placeholder="https://example.com">
										@if($errors->has('link'))
											<div class="invalid-feedback">{{ $errors->first('link') }}</div>
										@endif
										<small class="form-text text-muted">Optional: Where users will be redirected when they click the ad</small>
									</div>
								</div>

								<!-- NEW: Expiration date field -->
								<div class="form-row">
									<div class="form-group col-12 col-md-6">
										<label for="expiration_date">Expiration Date</label>
										<input type="datetime-local" class="form-control{{ $errors->has('expiration_date') ? ' is-invalid' : '' }}" value="{{ old('expiration_date', $ad->expiration_date ? $ad->expiration_date->format('Y-m-d\TH:i') : '') }}" id="expiration_date" name="expiration_date">
										@if($errors->has('expiration_date'))
											<div class="invalid-feedback">{{ $errors->first('expiration_date') }}</div>
										@endif
										<small class="form-text text-muted">Optional: When this ad should stop being displayed</small>
									</div>
									<div class="form-group col-12 col-md-6">
										<label for="image">Ad Image</label>
										<input type="file" class="form-control{{ $errors->has('image') ? ' is-invalid' : '' }}" id="image" name="image" accept="image/*">
										@if($errors->has('image'))
											<div class="invalid-feedback">{{ $errors->first('image') }}</div>
										@endif
										<small class="form-text text-muted">Upload an image for this ad (JPEG, PNG, JPG, GIF - Max: 2MB)</small>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Sidebar -->
					<div class="col-lg-4">
						<!-- Current Image -->
						@if($ad->image)
						<div class="card mb-4">
							<div class="card-header">
								<h6 class="mb-0">Current Image</h6>
							</div>
							<div class="card-body text-center">
								<img src="{{ asset('storage/' . $ad->image->path) }}" alt="Current Ad Image" class="img-fluid rounded" style="max-height: 200px;">
								<p class="text-muted mt-2 mb-0"><small>Upload a new image to replace this one</small></p>
							</div>
						</div>
						@endif

						<!-- Ad Status (Edit Mode Only) -->
						@if($ad->id)
						<div class="card mb-4">
							<div class="card-header">
								<h6 class="mb-0">Ad Status</h6>
							</div>
							<div class="card-body">
								<div class="text-center">
									@if($ad->expiration_date)
										@if($ad->expiration_date->isPast())
											<span class="badge badge-danger mb-2">Expired</span>
											<br><small class="text-muted">Expired {{ $ad->expiration_date->diffForHumans() }}</small>
										@else
											<span class="badge badge-success mb-2">Active</span>
											<br><small class="text-muted">Expires {{ $ad->expiration_date->diffForHumans() }}</small>
										@endif
									@else
										<span class="badge badge-primary mb-2">Active (No Expiration)</span>
										<br><small class="text-muted">This ad will run indefinitely</small>
									@endif
								</div>
								
								@if($ad->link)
								<hr>
								<div class="text-center">
									<strong>Link:</strong><br>
									<a href="{{ $ad->link }}" target="_blank" class="btn btn-outline-primary btn-sm">
										<i class="gd-link"></i> View Link
									</a>
								</div>
								@endif
							</div>
						</div>

						<!-- Related Links -->
						<div class="card">
							<div class="card-header">
								<h6 class="mb-0">Quick Links</h6>
							</div>
							<div class="card-body">
								<div class="d-grid gap-2">
									<a href="{{ route('ad.show', $ad) }}" class="btn btn-outline-primary btn-sm">
										<i class="gd-eye"></i> View Details
									</a>
									<a href="{{ route('ad.index') }}" class="btn btn-outline-secondary btn-sm">
										<i class="gd-arrow-left"></i> Back to Ads
									</a>
								</div>
							</div>
						</div>
						@else
						<!-- Help Card for New Ads -->
						<div class="card">
							<div class="card-header">
								<h6 class="mb-0">Help & Tips</h6>
							</div>
							<div class="card-body">
								<ul class="mb-0 small">
									<li>Choose a clear, descriptive name for your ad</li>
									<li>Select where this ad should appear in the app</li>
									<li><strong>NEW:</strong> Add a link URL to make the ad clickable</li> <!-- NEW -->
									<li><strong>NEW:</strong> Set an expiration date to automatically disable the ad</li> <!-- NEW -->
									<li>Select the appropriate city where this ad will be shown</li>
									<li>Upload a high-quality image that represents your ad</li>
									<li>Make sure the image is clear and readable at different sizes</li>
								</ul>
							</div>
						</div>
						@endif
					</div>
				</div>

				<!-- Form Actions -->
				<div class="card mt-4">
					<div class="card-body">
						<div class="d-flex justify-content-between align-items-center">
							<div>
								<a href="{{ $ad->id ? route('ad.show', $ad) : route('ad.index') }}" class="btn btn-secondary">
									<i class="gd-arrow-left"></i> {{ $ad->id ? 'Back to Details' : 'Back to List' }}
								</a>
							</div>
							<div>
								@if($ad->id)
								<a href="{{ route('ad.index') }}" class="btn btn-outline-secondary mr-2">Cancel</a>
								@endif
								<button type="submit" class="btn btn-primary">
									<i class="gd-{{ $ad->id ? 'check' : 'plus' }}"></i> {{ $ad->id ? 'Update Ad' : 'Create Ad' }}
								</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
		<!-- End Form -->
	</div>
</div>
@endsection