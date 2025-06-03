@extends('layouts.grain')

@section('title', 'Settings')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">

	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('setting.index') }}">Settings</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ $setting->id ? 'Edit' : 'Create New' }} Setting</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">{{ $setting->id ? 'Edit' : 'Create New' }} Setting</div>
		</div>

		<!-- Form -->
		<div>
			<form method="post" action="{{ $setting->id ? route('setting.update', $setting) : route('setting.store') }}">
                @if($setting->id)
				<input type="hidden" name="_method" value="patch">
                @endif
				@csrf
				
				<div class="form-row">
					<div class="form-group col-12 col-md-6">
						<label for="city_id">City <span class="text-danger">*</span></label>
						<select class="form-control{{ $errors->has('city_id') ? ' is-invalid' : '' }}" id="city_id" name="city_id" required>
							<option value="">Select City</option>
							@foreach($cities as $city)
								<option value="{{ $city->id }}" {{ old('city_id', $setting->city_id) == $city->id ? 'selected' : '' }}>
									{{ $city->name }}
								</option>
							@endforeach
						</select>
						@if($errors->has('city_id'))
							<div class="invalid-feedback">{{ $errors->first('city_id') }}</div>
						@endif
					</div>
					<div class="form-group col-12 col-md-6">
						<label for="setting_type">Setting Type <span class="text-danger">*</span></label>
						<select class="form-control{{ $errors->has('setting_type') ? ' is-invalid' : '' }}" id="setting_type" name="setting_type" required>
							@foreach($settingTypes as $key => $type)
								<option value="{{ $key }}" {{ old('setting_type', $setting->key == $key ? $key : 'custom') == $key ? 'selected' : '' }}>
									{{ $type }}
								</option>
							@endforeach
						</select>
						@if($errors->has('setting_type'))
							<div class="invalid-feedback">{{ $errors->first('setting_type') }}</div>
						@endif
					</div>
				</div>

				<div class="form-row" id="custom_key_row" style="{{ old('setting_type', $setting->key) == 'custom' || !in_array($setting->key, array_keys($settingTypes)) ? '' : 'display: none;' }}">
					<div class="form-group col-12">
						<label for="key">Custom Setting Key <span class="text-danger">*</span></label>
						<input type="text" class="form-control{{ $errors->has('key') ? ' is-invalid' : '' }}" value="{{ old('key', $setting->key) }}" id="key" name="key" placeholder="e.g., contact_email, app_version, etc.">
						<small class="form-text text-muted">Use lowercase letters, numbers, and underscores only. Must be unique per city.</small>
						@if($errors->has('key'))
							<div class="invalid-feedback">{{ $errors->first('key') }}</div>
						@endif
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-12">
						<label for="value">Setting Value <span class="text-danger">*</span></label>
						<textarea class="form-control{{ $errors->has('value') ? ' is-invalid' : '' }}" id="value" name="value" rows="8" placeholder="Enter the setting value..." required>{{ old('value', $setting->value) }}</textarea>
						<small class="form-text text-muted">You can use HTML tags for rich content like About Us, Terms, etc.</small>
						@if($errors->has('value'))
							<div class="invalid-feedback">{{ $errors->first('value') }}</div>
						@endif
					</div>
				</div>

				@if($setting->id)
				<div class="form-row">
					<div class="form-group col-12">
						<label>Setting Information</label>
						<div class="card bg-light">
							<div class="card-body">
								<div class="row">
									<div class="col-md-6">
										<strong>Current Key:</strong> {{ $setting->key }}<br>
										<strong>City:</strong> {{ $setting->city->name ?? 'N/A' }}
									</div>
									<div class="col-md-6">
										<strong>Created:</strong> {{ $setting->created_at->format('M d, Y H:i') }}<br>
										<strong>Last Updated:</strong> {{ $setting->updated_at->format('M d, Y H:i') }}
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				@endif

				<div class="d-flex justify-content-between">
					<a href="{{ route('setting.index') }}" class="btn btn-secondary">Cancel</a>
					<button type="submit" class="btn btn-primary">{{ $setting->id ? 'Update' : 'Create' }} Setting</button>
				</div>
			</form>
		</div>
		<!-- End Form -->
	</div>
</div>

<script>
document.getElementById('setting_type').addEventListener('change', function() {
	const customKeyRow = document.getElementById('custom_key_row');
	const keyInput = document.getElementById('key');
	
	if(this.value === 'custom') {
		customKeyRow.style.display = '';
		keyInput.required = true;
	} else {
		customKeyRow.style.display = 'none';
		keyInput.required = false;
		keyInput.value = this.value;
	}
});

// Update key field when setting type changes (for non-custom types)
document.addEventListener('DOMContentLoaded', function() {
	const settingType = document.getElementById('setting_type');
	const keyInput = document.getElementById('key');
	
	settingType.addEventListener('change', function() {
		if(this.value !== 'custom') {
			keyInput.value = this.value;
		}
	});
});
</script>

@endsection