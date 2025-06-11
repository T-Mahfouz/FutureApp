@extends('layouts.grain')

@section('title', 'Send Firebase Notification')

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
				<li class="breadcrumb-item active" aria-current="page">Send Firebase Notification</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">Send Firebase Notification Only</div>
		</div>

		<div class="alert alert-info">
			<i class="gd-info-alt"></i>
			<strong>Note:</strong> This will send a Firebase notification only without saving it to the database.
		</div>

		<!-- Form -->
		<div>
			<form method="post" action="{{ route('notification.send-firebase-post') }}">
				@csrf
				
				<div class="form-row">
					<div class="form-group col-12 col-md-6">
						<label for="title">Notification Title</label>
						<input type="text" class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}" value="{{ old('title') }}" id="title" name="title" placeholder="Enter notification title" required>
						@if($errors->has('title'))
							<div class="invalid-feedback">{{ $errors->first('title') }}</div>
						@endif
					</div>
					<div class="form-group col-12 col-md-6">
						<label for="target_type">Target Type</label>
						<select class="form-control{{ $errors->has('target_type') ? ' is-invalid' : '' }}" id="target_type" name="target_type" required>
							<option value="">Select Target Type</option>
							<option value="broadcast" {{ old('target_type') == 'broadcast' ? 'selected' : '' }}>Broadcast (All Users)</option>
							<option value="cities" {{ old('target_type') == 'cities' ? 'selected' : '' }}>Specific Cities</option>
							<option value="service" {{ old('target_type') == 'service' ? 'selected' : '' }}>Related to Service</option>
							<option value="news" {{ old('target_type') == 'news' ? 'selected' : '' }}>Related to News</option>
						</select>
						@if($errors->has('target_type'))
							<div class="invalid-feedback">{{ $errors->first('target_type') }}</div>
						@endif
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-12">
						<label for="body">Notification Body</label>
						<textarea class="form-control{{ $errors->has('body') ? ' is-invalid' : '' }}" id="body" name="body" rows="4" placeholder="Enter notification content" required>{{ old('body') }}</textarea>
						@if($errors->has('body'))
							<div class="invalid-feedback">{{ $errors->first('body') }}</div>
						@endif
					</div>
				</div>

				<!-- Cities Selection (Hidden by default) -->
				<div class="form-row" id="cities_selection" style="display: none;">
					<div class="form-group col-12">
						<label for="city_ids">Select Cities</label>
						<select class="form-control{{ $errors->has('city_ids') ? ' is-invalid' : '' }}" id="city_ids" name="city_ids[]" multiple>
							@foreach($cities as $city)
								<option value="{{ $city->id }}" {{ in_array($city->id, old('city_ids', [])) ? 'selected' : '' }}>{{ $city->name }}</option>
							@endforeach
						</select>
						@if($errors->has('city_ids'))
							<div class="invalid-feedback">{{ $errors->first('city_ids') }}</div>
						@endif
						<small class="form-text text-muted">Hold Ctrl/Cmd to select multiple cities</small>
					</div>
				</div>

				<!-- Service Selection (Hidden by default) -->
				<div class="form-row" id="service_selection" style="display: none;">
					<div class="form-group col-12">
						<label for="service_id">Select Service</label>
						<select class="form-control{{ $errors->has('service_id') ? ' is-invalid' : '' }}" id="service_id" name="service_id">
							<option value="">Select Service</option>
							@foreach($services as $service)
								<option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
									{{ $service->name }} ({{ $service->city->name ?? 'No City' }})
								</option>
							@endforeach
						</select>
						@if($errors->has('service_id'))
							<div class="invalid-feedback">{{ $errors->first('service_id') }}</div>
						@endif
					</div>
				</div>

				<!-- News Selection (Hidden by default) -->
				<div class="form-row" id="news_selection" style="display: none;">
					<div class="form-group col-12">
						<label for="news_id">Select News</label>
						<select class="form-control{{ $errors->has('news_id') ? ' is-invalid' : '' }}" id="news_id" name="news_id">
							<option value="">Select News</option>
							@foreach($news as $newsItem)
								<option value="{{ $newsItem->id }}" {{ old('news_id') == $newsItem->id ? 'selected' : '' }}>{{ $newsItem->name }}</option>
							@endforeach
						</select>
						@if($errors->has('news_id'))
							<div class="invalid-feedback">{{ $errors->first('news_id') }}</div>
						@endif
					</div>
				</div>

				<div class="d-flex justify-content-between">
					<a href="{{ route('notification.index') }}" class="btn btn-secondary">Cancel</a>
					<button type="submit" class="btn btn-success">
						<i class="gd-rocket"></i> Send Firebase Notification
					</button>
				</div>
			</form>
		</div>
		<!-- End Form -->
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const targetTypeSelect = document.getElementById('target_type');
	const citiesSelection = document.getElementById('cities_selection');
	const serviceSelection = document.getElementById('service_selection');
	const newsSelection = document.getElementById('news_selection');

	function toggleSelections() {
		const targetType = targetTypeSelect.value;
		
		// Hide all selections first
		citiesSelection.style.display = 'none';
		serviceSelection.style.display = 'none';
		newsSelection.style.display = 'none';
		
		// Show relevant selection based on target type
		switch(targetType) {
			case 'cities':
				citiesSelection.style.display = 'block';
				break;
			case 'service':
				serviceSelection.style.display = 'block';
				break;
			case 'news':
				newsSelection.style.display = 'block';
				break;
		}
	}

	targetTypeSelect.addEventListener('change', toggleSelections);
	
	// Initialize on page load
	toggleSelections();
});
</script>

@endsection