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
				
				<div class="form-row">
					<div class="form-group col-12 col-md-6">
						<label for="name">Ad Name</label>
						<input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name', $ad->name) }}" id="name" name="name" placeholder="Enter ad name" required>
						@if($errors->has('name'))
							<div class="invalid-feedback">{{ $errors->first('name') }}</div>
						@endif
					</div>
					<div class="form-group col-12 col-md-6">
						<label for="location">Location</label>
						<select class="form-control{{ $errors->has('location') ? ' is-invalid' : '' }}" id="location" name="location" required>
							<option value="">Select Location</option>
							<option value="home" {{ old('location', $ad->location) == 'home' ? 'selected' : '' }}>Home</option>
							<option value="category_profile" {{ old('location', $ad->location) == 'category_profile' ? 'selected' : '' }}>Category Profile</option>
							<option value="service_profile" {{ old('location', $ad->location) == 'service_profile' ? 'selected' : '' }}>Service Profile</option>
						</select>
						@if($errors->has('location'))
							<div class="invalid-feedback">{{ $errors->first('location') }}</div>
						@endif
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-12 col-md-6">
						<label for="city_id">City</label>
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
					<div class="form-group col-12 col-md-6">
						<label for="image">Ad Image</label>
						<input type="file" class="form-control{{ $errors->has('image') ? ' is-invalid' : '' }}" id="image" name="image" accept="image/*">
						@if($errors->has('image'))
							<div class="invalid-feedback">{{ $errors->first('image') }}</div>
						@endif
						<small class="form-text text-muted">Upload an image for this ad</small>
					</div>
				</div>

				@if($ad->image)
				<div class="form-row">
					<div class="form-group col-12">
						<label>Current Image</label>
						<div class="mt-2">
							<img src="{{ asset('storage/' . $ad->image->path) }}" alt="Current Ad Image" class="img-thumbnail" style="max-width: 300px;">
							<p class="text-muted mt-1">Current ad image - upload a new one to replace it</p>
						</div>
					</div>
				</div>
				@endif

				<div class="d-flex justify-content-between">
					<a href="{{ route('ad.index') }}" class="btn btn-secondary">Cancel</a>
					<button type="submit" class="btn btn-primary">{{ $ad->id ? 'Update' : 'Create' }} Ad</button>
				</div>
			</form>
		</div>
		<!-- End Form -->
	</div>
</div>
@endsection