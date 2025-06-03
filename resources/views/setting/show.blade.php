@extends('layouts.grain')

@section('title', 'Setting Details')

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
				<li class="breadcrumb-item active" aria-current="page">{{ $setting->key }}</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
			<div>
				<h3 class="mb-0">{{ ucwords(str_replace('_', ' ', $setting->key)) }}</h3>
				<small class="text-muted">Setting for {{ $setting->city->name ?? 'Unknown City' }}</small>
			</div>
			<div>
				<a href="{{ route('setting.edit', $setting) }}" class="btn btn-primary">
					<i class="gd-pencil"></i> Edit Setting
				</a>
			</div>
		</div>

		<!-- Setting Details -->
		<div class="row">
			<div class="col-md-4">
				<div class="card">
					<div class="card-header">
						<h5 class="mb-0">Setting Information</h5>
					</div>
					<div class="card-body">
						<table class="table table-borderless">
							<tr>
								<td><strong>Key:</strong></td>
								<td>{{ $setting->key }}</td>
							</tr>
							<tr>
								<td><strong>City:</strong></td>
								<td>
									<span class="badge badge-primary">{{ $setting->city->name ?? 'N/A' }}</span>
								</td>
							</tr>
							<tr>
								<td><strong>Type:</strong></td>
								<td>
									@php
										$settingTypes = [
											'about_us' => 'About Us',
											'terms_conditions' => 'Terms & Conditions',
											'privacy_policy' => 'Privacy Policy',
											'contact_info' => 'Contact Information',
											'help_support' => 'Help & Support',
											'app_config' => 'App Configuration',
											'social_media' => 'Social Media Links',
											'notification_settings' => 'Notification Settings',
										];
										
										$type = 'Custom Setting';
										foreach($settingTypes as $key => $label){
											if(str_starts_with($setting->key, $key)){
												$type = $label;
												break;
											}
										}
									@endphp
									<span class="badge badge-secondary">{{ $type }}</span>
								</td>
							</tr>
							<tr>
								<td><strong>Created:</strong></td>
								<td>{{ $setting->created_at->format('M d, Y H:i') }}</td>
							</tr>
							<tr>
								<td><strong>Updated:</strong></td>
								<td>{{ $setting->updated_at->format('M d, Y H:i') }}</td>
							</tr>
							<tr>
								<td><strong>Content Length:</strong></td>
								<td>{{ strlen($setting->value) }} characters</td>
							</tr>
						</table>
					</div>
				</div>

				@if($setting->city)
				<div class="card mt-3">
					<div class="card-header">
						<h5 class="mb-0">City Information</h5>
					</div>
					<div class="card-body">
						<div class="d-flex align-items-center">
							@if($setting->city->image)
								<img src="{{ asset('storage/' . $setting->city->image->path) }}" alt="{{ $setting->city->name }}" class="rounded mr-3" width="50" height="50">
							@endif
							<div>
								<strong>{{ $setting->city->name }}</strong>
								<br><small class="text-muted">{{ $setting->city->services()->count() }} services</small>
							</div>
						</div>
					</div>
				</div>
				@endif
			</div>

			<div class="col-md-8">
				<div class="card">
					<div class="card-header d-flex justify-content-between align-items-center">
						<h5 class="mb-0">Setting Value</h5>
						<div>
							<button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleView()">
								<span id="view-toggle">Raw View</span>
							</button>
						</div>
					</div>
					<div class="card-body">
						<!-- Rendered View -->
						<div id="rendered-view">
							<div class="border rounded p-3" style="background-color: #f8f9fa; min-height: 200px;">
								{!! nl2br(e($setting->value)) !!}
							</div>
						</div>
						
						<!-- Raw View -->
						<div id="raw-view" style="display: none;">
							<pre class="border rounded p-3" style="background-color: #f8f9fa; min-height: 200px; white-space: pre-wrap;">{{ $setting->value }}</pre>
						</div>
					</div>
				</div>

				<!-- Quick Actions -->
				<div class="card mt-3">
					<div class="card-header">
						<h5 class="mb-0">Quick Actions</h5>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-6">
								<button type="button" class="btn btn-outline-primary btn-block" onclick="copyToClipboard()">
									<i class="gd-clipboard"></i> Copy Value
								</button>
							</div>
							<div class="col-md-6">
								<a href="{{ route('setting.index', ['city_id' => $setting->city_id]) }}" class="btn btn-outline-info btn-block">
									<i class="gd-eye"></i> View City Settings
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>

<script>
function toggleView() {
	const renderedView = document.getElementById('rendered-view');
	const rawView = document.getElementById('raw-view');
	const toggleButton = document.getElementById('view-toggle');
	
	if (rawView.style.display === 'none') {
		rawView.style.display = 'block';
		renderedView.style.display = 'none';
		toggleButton.textContent = 'Rendered View';
	} else {
		rawView.style.display = 'none';
		renderedView.style.display = 'block';
		toggleButton.textContent = 'Raw View';
	}
}

function copyToClipboard() {
	const value = @json($setting->value);
	navigator.clipboard.writeText(value).then(function() {
		alert('Setting value copied to clipboard!');
	}, function(err) {
		alert('Could not copy text: ', err);
	});
}
</script>

@endsection