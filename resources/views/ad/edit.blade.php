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
			<form method="post" action="{{ $ad->id ? route('ad.update', $ad) : route('ad.store') }}" enctype="multipart/form-data" id="adForm">
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
											<option value="all_locations" {{ old('location', $ad->location) == 'all_locations' ? 'selected' : '' }}>All Locations</option>
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

								<!-- Category and Service Selection -->
								<div class="form-row">
									<div class="form-group col-12 col-md-6">
										<label for="category_id">Category</label>
										<select class="form-control{{ $errors->has('category_id') ? ' is-invalid' : '' }}" id="category_id" name="category_id">
											<option value="">Select Category (Optional)</option>
											@if(isset($categories))
												@foreach($categories as $category)
													<option value="{{ $category->id }}" {{ old('category_id', $ad->category_id) == $category->id ? 'selected' : '' }}>
														{{ $category->name }}
													</option>
												@endforeach
											@endif
										</select>
										@if($errors->has('category_id'))
											<div class="invalid-feedback">{{ $errors->first('category_id') }}</div>
										@endif
										<small class="form-text text-muted">Select a city first to load categories</small>
									</div>
									<div class="form-group col-12 col-md-6">
										<label for="service_id">Service</label>
										<select class="form-control{{ $errors->has('service_id') ? ' is-invalid' : '' }}" id="service_id" name="service_id">
											<option value="">Select Service (Optional)</option>
											@if(isset($services))
												@foreach($services as $service)
													<option value="{{ $service->id }}" {{ old('service_id', $ad->service_id) == $service->id ? 'selected' : '' }}>
														{{ $service->name }}
													</option>
												@endforeach
											@endif
										</select>
										@if($errors->has('service_id'))
											<div class="invalid-feedback">{{ $errors->first('service_id') }}</div>
										@endif
										<small class="form-text text-muted">Select a category to filter services</small>
									</div>
								</div>

								<!-- Link field -->
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

								<!-- Expiration date field -->
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

						<!-- Assignment Summary -->
						<div class="card mb-4">
							<div class="card-header">
								<h6 class="mb-0">Assignment Summary</h6>
							</div>
							<div class="card-body">
								<div class="text-center">
									<div class="mb-3">
										<strong>City:</strong><br>
										<span id="selected-city" class="badge badge-info">
											{{ $ad->city ? $ad->city->name : 'None Selected' }}
										</span>
									</div>
									<div class="mb-3">
										<strong>Category:</strong><br>
										<span id="selected-category" class="badge badge-success">
											{{ $ad->category ? $ad->category->name : 'None Selected' }}
										</span>
									</div>
									<div class="mb-3">
										<strong>Service:</strong><br>
										<span id="selected-service" class="badge badge-primary">
											{{ $ad->service ? $ad->service->name : 'None Selected' }}
										</span>
									</div>
								</div>
							</div>
						</div>

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
									<li><strong>Required:</strong> Select a city for this ad</li>
									<li><strong>Optional:</strong> Assign to a category to target specific interests</li>
									<li><strong>Optional:</strong> Assign to a service for more targeted placement</li>
									<li>Add a link URL to make the ad clickable</li>
									<li>Set an expiration date to automatically disable the ad</li>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
	const citySelect = document.getElementById('city_id');
	const categorySelect = document.getElementById('category_id');
	const serviceSelect = document.getElementById('service_id');
	
	// Update summary badges
	function updateSummary() {
		const selectedCity = citySelect.options[citySelect.selectedIndex].text;
		const selectedCategory = categorySelect.options[categorySelect.selectedIndex].text;
		const selectedService = serviceSelect.options[serviceSelect.selectedIndex].text;
		
		document.getElementById('selected-city').textContent = citySelect.value ? selectedCity : 'None Selected';
		document.getElementById('selected-category').textContent = categorySelect.value ? selectedCategory : 'None Selected';
		document.getElementById('selected-service').textContent = serviceSelect.value ? selectedService : 'None Selected';
	}

	// Load categories when city changes
	citySelect.addEventListener('change', function() {
		const cityId = this.value;
		
		// Clear category and service dropdowns
		categorySelect.innerHTML = '<option value="">Select Category (Optional)</option>';
		serviceSelect.innerHTML = '<option value="">Select Service (Optional)</option>';
		
		updateSummary();
		
		let url = `{{ route('ad.categories-by-city') }}?city_id=${cityId}`;

		if (cityId) {
			$.ajax({
				url: url,
				type: 'GET',
				dataType: 'json',
				success: function(data) {
					if (data.error) {
						console.error('Error loading categories:', data.error);
						return;
					}
					
					data.forEach(category => {
						const option = document.createElement('option');
						option.value = category.id;
						option.textContent = category.name;
						categorySelect.appendChild(option);
					});
					
					// Restore selected category if editing
					@if($ad->category_id)
						categorySelect.value = '{{ $ad->category_id }}';
						updateSummary();
						// Trigger category change to load services
						categorySelect.dispatchEvent(new Event('change'));
					@endif
				},
				error: function(xhr, status, error) {
					console.error('Error loading categories:', error);
				}
			});
		}
	});

	// Load services when category changes
	categorySelect.addEventListener('change', function() {
		const cityId = citySelect.value;
		const categoryId = this.value;
		
		// Clear service dropdown
		serviceSelect.innerHTML = '<option value="">Select Service (Optional)</option>';
		
		updateSummary();
		
		if (cityId) {

			
			let url = `{{ route('ad.services-by-city-category') }}?city_id=${cityId}`;
			if (categoryId) {
				url += `&category_id=${categoryId}`;
			}
			

			$.ajax({
				url: url,
				type: 'GET',
				dataType: 'json',
				success: function(data) {
					if (data.error) {
						console.error('Error loading services:', data.error);
						return;
					}
					
					data.forEach(service => {
						const option = document.createElement('option');
						option.value = service.id;
						option.textContent = service.name;
						serviceSelect.appendChild(option);
					});
					
					// Restore selected service if editing
					@if($ad->service_id)
						serviceSelect.value = '{{ $ad->service_id }}';
						updateSummary();
					@endif
				},
				error: function(xhr, status, error) {
					console.error('Error loading services:', error);
				}
			});
		}
	});

	// Update summary when service changes
	serviceSelect.addEventListener('change', updateSummary);

	// Initialize summary
	updateSummary();
	
	// If editing, trigger city change to load categories and services
	@if($ad->city_id)
		citySelect.dispatchEvent(new Event('change'));
	@endif
});
</script>

@endsection