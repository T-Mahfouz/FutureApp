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
					<a href="{{ route('service.index') }}">Services</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ $service->id ? 'Edit' : 'Create New' }} Service</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">{{ $service->id ? 'Edit' : 'Create New' }} Service</div>
		</div>

		<!-- Form -->
		<form method="post" action="{{ $service->id ? route('service.update', $service) : route('service.store') }}" enctype="multipart/form-data">
			@if($service->id)
			<input type="hidden" name="_method" value="patch">
			@endif
			@csrf

			<!-- Basic Information -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="mb-0">Basic Information</h5>
				</div>
				<div class="card-body">
					<div class="form-row">
						<div class="form-group col-12 col-md-6">
							<label for="name">Service Name *</label>
							<input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" 
								   value="{{ old('name', $service->name) }}" id="name" name="name" 
								   placeholder="Enter service name" required>
							@if($errors->has('name'))
								<div class="invalid-feedback">{{ $errors->first('name') }}</div>
							@endif
						</div>

						<div class="form-group col-12 col-md-6">
							<label for="city_id">City *</label>
							<select class="form-control{{ $errors->has('city_id') ? ' is-invalid' : '' }}" id="city_id" name="city_id" required>
								<option value="">Select City</option>
								@foreach($cities as $city)
									<option value="{{ $city->id }}" {{ old('city_id', $service->city_id) == $city->id ? 'selected' : '' }}>
										{{ $city->name }}
									</option>
								@endforeach
							</select>
							@if($errors->has('city_id'))
								<div class="invalid-feedback">{{ $errors->first('city_id') }}</div>
							@endif
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-12 col-md-6">
							<label for="phone">Primary Phone</label>
							<input type="text" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" 
								   value="{{ old('phone', $service->phones && $service->phones->count() > 0 ? $service->phones->first()->phone : '') }}" id="phone" name="phone" 
								   placeholder="Enter primary phone number">
							@if($errors->has('phone'))
								<div class="invalid-feedback">{{ $errors->first('phone') }}</div>
							@endif
						</div>

						<div class="form-group col-12 col-md-6">
							<label for="whatsapp">WhatsApp</label>
							<input type="text" class="form-control{{ $errors->has('whatsapp') ? ' is-invalid' : '' }}" 
								   value="{{ old('whatsapp', $service->whatsapp) }}" id="whatsapp" name="whatsapp" 
								   placeholder="Enter WhatsApp number">
							@if($errors->has('whatsapp'))
								<div class="invalid-feedback">{{ $errors->first('whatsapp') }}</div>
							@endif
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-12">
							<label for="brief_description">Brief Description</label>
							<textarea class="form-control{{ $errors->has('brief_description') ? ' is-invalid' : '' }}" 
									  id="brief_description" name="brief_description" rows="2" 
									  placeholder="Brief description (max 500 characters)">{{ old('brief_description', $service->brief_description) }}</textarea>
							@if($errors->has('brief_description'))
								<div class="invalid-feedback">{{ $errors->first('brief_description') }}</div>
							@endif
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-12">
							<label for="description">Full Description</label>
							<textarea class="form-control{{ $errors->has('description') ? ' is-invalid' : '' }}" 
									  id="description" name="description" rows="4" 
									  placeholder="Full service description">{{ old('description', $service->description) }}</textarea>
							@if($errors->has('description'))
								<div class="invalid-feedback">{{ $errors->first('description') }}</div>
							@endif
						</div>
					</div>
				</div>
			</div>

			<!-- Categories and Parent Service -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="mb-0">Categories and Hierarchy</h5>
				</div>
				<div class="card-body">
					<div class="form-row">
						<div class="form-group col-12 col-md-6">
							<label for="categories">Categories</label>
							<select class="form-control{{ $errors->has('categories') ? ' is-invalid' : '' }}" 
									id="categories" name="categories[]" multiple>
								@foreach($categories as $category)
									<option value="{{ $category->id }}" 
											{{ in_array($category->id, old('categories', $service->categories->pluck('id')->toArray())) ? 'selected' : '' }}>
										{{ $category->name }}
									</option>
								@endforeach
							</select>
							@if($errors->has('categories'))
								<div class="invalid-feedback">{{ $errors->first('categories') }}</div>
							@endif
							<small class="form-text text-muted">Hold Ctrl/Cmd to select multiple categories</small>
						</div>

						<div class="form-group col-12 col-md-6">
							<label for="parent_id">Parent Service (Optional)</label>
							<select class="form-control{{ $errors->has('parent_id') ? ' is-invalid' : '' }}" 
									id="parent_id" name="parent_id">
								<option value="">No Parent (Main Service)</option>
								@foreach($parentServices as $parentService)
									<option value="{{ $parentService->id }}" 
											{{ old('parent_id', $service->parent_id) == $parentService->id ? 'selected' : '' }}>
										{{ $parentService->name }}
									</option>
								@endforeach
							</select>
							@if($errors->has('parent_id'))
								<div class="invalid-feedback">{{ $errors->first('parent_id') }}</div>
							@endif
							<small class="form-text text-muted">Select to make this a sub-service</small>
						</div>
					</div>
				</div>
			</div>

			<!-- Location Information -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="mb-0">Location Information</h5>
				</div>
				<div class="card-body">
					<div class="form-row">
						<div class="form-group col-12">
							<label for="address">Address</label>
							<textarea class="form-control{{ $errors->has('address') ? ' is-invalid' : '' }}" 
									  id="address" name="address" rows="2" 
									  placeholder="Physical address">{{ old('address', $service->address) }}</textarea>
							@if($errors->has('address'))
								<div class="invalid-feedback">{{ $errors->first('address') }}</div>
							@endif
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-12 col-md-6">
							<label for="lat">Latitude</label>
							<input type="number" step="any" class="form-control{{ $errors->has('lat') ? ' is-invalid' : '' }}" 
								   value="{{ old('lat', $service->lat) }}" id="lat" name="lat" 
								   placeholder="e.g. 31.2001">
							@if($errors->has('lat'))
								<div class="invalid-feedback">{{ $errors->first('lat') }}</div>
							@endif
						</div>

						<div class="form-group col-12 col-md-6">
							<label for="lon">Longitude</label>
							<input type="number" step="any" class="form-control{{ $errors->has('lon') ? ' is-invalid' : '' }}" 
								   value="{{ old('lon', $service->lon) }}" id="lon" name="lon" 
								   placeholder="e.g. 29.9187">
							@if($errors->has('lon'))
								<div class="invalid-feedback">{{ $errors->first('lon') }}</div>
							@endif
						</div>
					</div>
				</div>
			</div>

			<!-- Social Media and Links -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="mb-0">Social Media and Links</h5>
				</div>
				<div class="card-body">
					<div class="form-row">
						<div class="form-group col-12 col-md-6">
							<label for="website">Website</label>
							<input type="url" class="form-control{{ $errors->has('website') ? ' is-invalid' : '' }}" 
								   value="{{ old('website', $service->website) }}" id="website" name="website" 
								   placeholder="https://example.com">
							@if($errors->has('website'))
								<div class="invalid-feedback">{{ $errors->first('website') }}</div>
							@endif
						</div>

						<div class="form-group col-12 col-md-6">
							<label for="video_link">Video Link</label>
							<input type="url" class="form-control{{ $errors->has('video_link') ? ' is-invalid' : '' }}" 
								   value="{{ old('video_link', $service->video_link) }}" id="video_link" name="video_link" 
								   placeholder="https://youtube.com/watch?v=...">
							@if($errors->has('video_link'))
								<div class="invalid-feedback">{{ $errors->first('video_link') }}</div>
							@endif
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-12 col-md-6">
							<label for="facebook">Facebook</label>
							<input type="url" class="form-control{{ $errors->has('facebook') ? ' is-invalid' : '' }}" 
								   value="{{ old('facebook', $service->facebook) }}" id="facebook" name="facebook" 
								   placeholder="https://facebook.com/page">
							@if($errors->has('facebook'))
								<div class="invalid-feedback">{{ $errors->first('facebook') }}</div>
							@endif
						</div>

						<div class="form-group col-12 col-md-6">
							<label for="instagram">Instagram</label>
							<input type="url" class="form-control{{ $errors->has('instagram') ? ' is-invalid' : '' }}" 
								   value="{{ old('instagram', $service->instagram) }}" id="instagram" name="instagram" 
								   placeholder="https://instagram.com/account">
							@if($errors->has('instagram'))
								<div class="invalid-feedback">{{ $errors->first('instagram') }}</div>
							@endif
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-12 col-md-6">
							<label for="youtube">YouTube</label>
							<input type="url" class="form-control{{ $errors->has('youtube') ? ' is-invalid' : '' }}" 
								   value="{{ old('youtube', $service->youtube) }}" id="youtube" name="youtube" 
								   placeholder="https://youtube.com/channel">
							@if($errors->has('youtube'))
								<div class="invalid-feedback">{{ $errors->first('youtube') }}</div>
							@endif
						</div>

						<div class="form-group col-12 col-md-6">
							<label for="telegram">Telegram</label>
							<input type="url" class="form-control{{ $errors->has('telegram') ? ' is-invalid' : '' }}" 
								   value="{{ old('telegram', $service->telegram) }}" id="telegram" name="telegram" 
								   placeholder="https://t.me/channel">
							@if($errors->has('telegram'))
								<div class="invalid-feedback">{{ $errors->first('telegram') }}</div>
							@endif
						</div>
					</div>
				</div>
			</div>

			<!-- Images -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="mb-0">Images</h5>
				</div>
				<div class="card-body">
					<div class="form-row">
						<div class="form-group col-12 col-md-6">
							<label for="image">Main Image</label>
							<input type="file" class="form-control{{ $errors->has('image') ? ' is-invalid' : '' }}" 
								   id="image" name="image" accept="image/*">
							@if($errors->has('image'))
								<div class="invalid-feedback">{{ $errors->first('image') }}</div>
							@endif
							<small class="form-text text-muted">Upload a main image for this service</small>
						</div>

						<div class="form-group col-12 col-md-6">
							<label for="additional_images">Additional Images</label>
							<input type="file" class="form-control{{ $errors->has('additional_images') ? ' is-invalid' : '' }}" 
								   id="additional_images" name="additional_images[]" accept="image/*" multiple>
							@if($errors->has('additional_images'))
								<div class="invalid-feedback">{{ $errors->first('additional_images') }}</div>
							@endif
							<small class="form-text text-muted">Upload additional images (hold Ctrl/Cmd to select multiple)</small>
						</div>
					</div>

					@if($service->image)
					<div class="form-row">
						<div class="form-group col-12">
							<label>Current Main Image</label>
							<div class="mt-2">
								<img src="{{ asset('storage/' . $service->image->path) }}" alt="Current Service Image" 
									 class="img-thumbnail" style="max-width: 200px;">
								<p class="text-muted mt-1">Current main image - upload a new one to replace it</p>
							</div>
						</div>
					</div>
					@endif

					@if($service->images && $service->images->count() > 0)
					<div class="form-row">
						<div class="form-group col-12">
							<label>Current Additional Images</label>
							<div class="mt-2 d-flex flex-wrap">
								@foreach($service->images as $image)
									<div class="mr-2 mb-2">
										<img src="{{ asset('storage/' . $image->path) }}" alt="Service Image" 
											 class="img-thumbnail" style="max-width: 100px;">
									</div>
								@endforeach
							</div>
							<p class="text-muted mt-1">Current additional images</p>
						</div>
					</div>
					@endif
				</div>
			</div>

			<!-- Additional Phone Numbers -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="mb-0">Additional Phone Numbers</h5>
				</div>
				<div class="card-body">
					<div id="phone-container">
						@if($service->phones && $service->phones->count() > 1)
							@foreach($service->phones->skip(1) as $index => $phoneObj)
								<div class="form-row phone-row">
									<div class="form-group col-10">
										<input type="text" class="form-control" name="phones[]" 
											   value="{{ old('phones.' . $index, $phoneObj->phone) }}" 
											   placeholder="Enter additional phone number">
									</div>
									<div class="form-group col-2">
										<button type="button" class="btn btn-danger remove-phone">Remove</button>
									</div>
								</div>
							@endforeach
						@else
							<div class="form-row phone-row">
								<div class="form-group col-10">
									<input type="text" class="form-control" name="phones[]" 
										   placeholder="Enter additional phone number">
								</div>
								<div class="form-group col-2">
									<button type="button" class="btn btn-danger remove-phone">Remove</button>
								</div>
							</div>
						@endif
					</div>
					<button type="button" class="btn btn-secondary" id="add-phone">Add Another Phone</button>
				</div>
			</div>

			<!-- Settings -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="mb-0">Settings</h5>
				</div>
				<div class="card-body">
					<div class="form-row">
						<div class="form-group col-12 col-md-4">
							<label for="arrangement_order">Display Order</label>
							<input type="number" class="form-control{{ $errors->has('arrangement_order') ? ' is-invalid' : '' }}" 
								   value="{{ old('arrangement_order', $service->arrangement_order ?? 1) }}" 
								   id="arrangement_order" name="arrangement_order" min="1">
							@if($errors->has('arrangement_order'))
								<div class="invalid-feedback">{{ $errors->first('arrangement_order') }}</div>
							@endif
							<small class="form-text text-muted">Lower numbers appear first</small>
						</div>

						<div class="form-group col-12 col-md-4">
							<div class="form-check mt-4">
								<input type="checkbox" class="form-check-input" id="valid" name="valid" value="1"
									   {{ old('valid', $service->valid) ? 'checked' : '' }}>
								<label class="form-check-label" for="valid">
									Active Status
								</label>
								<small class="form-text text-muted">Check to make service visible</small>
							</div>
						</div>

						<div class="form-group col-12 col-md-4">
							<div class="form-check mt-4">
								<input type="checkbox" class="form-check-input" id="is_add" name="is_add" value="1"
									   {{ old('is_add', $service->is_add) ? 'checked' : '' }}>
								<label class="form-check-label" for="is_add">
									Advertisement
								</label>
								<small class="form-text text-muted">Mark as advertisement</small>
							</div>
						</div>
					</div>
				</div>
			</div>

			@if($service->id)
			<!-- Service Statistics -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="mb-0">Service Statistics</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-3">
							<div class="card bg-info text-white">
								<div class="card-body text-center">
									<h4>{{ $service->rates()->count() }}</h4>
									<small>Ratings</small>
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
									<h4>{{ $service->categories()->count() }}</h4>
									<small>Categories</small>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="card bg-secondary text-white">
								<div class="card-body text-center">
									<h4>{{ $service->subServices()->count() }}</h4>
									<small>Sub-services</small>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			@endif

			<div class="d-flex justify-content-between">
				<a href="{{ route('service.index') }}" class="btn btn-secondary">Cancel</a>
				<button type="submit" class="btn btn-primary">{{ $service->id ? 'Update' : 'Create' }} Service</button>
			</div>
		</form>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add phone number functionality
    document.getElementById('add-phone').addEventListener('click', function() {
        const container = document.getElementById('phone-container');
        const newRow = document.createElement('div');
        newRow.className = 'form-row phone-row';
        newRow.innerHTML = `
            <div class="form-group col-10">
                <input type="text" class="form-control" name="phones[]" placeholder="Enter additional phone number">
            </div>
            <div class="form-group col-2">
                <button type="button" class="btn btn-danger remove-phone">Remove</button>
            </div>
        `;
        container.appendChild(newRow);
    });

    // Remove phone number functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-phone')) {
            const phoneRows = document.querySelectorAll('.phone-row');
            if (phoneRows.length > 1) {
                e.target.closest('.phone-row').remove();
            }
        }
    });
});
</script>

@endsection