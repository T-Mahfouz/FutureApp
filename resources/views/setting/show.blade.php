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
				<h3 class="mb-0">{{ $setting->key }}</h3>
				<small class="text-muted">
					City: {{ $setting->city->name ?? 'No City' }} | 
					Created {{ $setting->created_at ? $setting->created_at->format('M d, Y') : 'Unknown' }}
				</small>
			</div>
			<div>
				<a href="{{ route('setting.edit', $setting) }}" class="btn btn-primary">
					<i class="gd-pencil"></i> Edit Setting
				</a>
			</div>
		</div>

		<!-- Setting Details -->
		<div class="row">
			<div class="col-md-3">
				<div class="card">
					<div class="card-header">
						<h5 class="mb-0">Setting Information</h5>
					</div>
					<div class="card-body">
						<div class="mb-3">
							<strong>City:</strong>
							<p class="text-muted mb-2">{{ $setting->city->name ?? 'No City' }}</p>
						</div>
						<div class="mb-3">
							<strong>Key:</strong>
							<p class="text-muted mb-2">{{ $setting->key }}</p>
						</div>
						<div class="mb-3">
							<strong>Created:</strong>
							<p class="text-muted mb-2">{{ $setting->created_at ? $setting->created_at->format('M d, Y H:i') : 'Unknown' }}</p>
						</div>
						<div class="mb-3">
							<strong>Updated:</strong>
							<p class="text-muted mb-0">{{ $setting->updated_at ? $setting->updated_at->format('M d, Y H:i') : 'Unknown' }}</p>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-9">
				<div class="card">
					<div class="card-header">
						<h5 class="mb-0">Setting Value</h5>
					</div>
					<div class="card-body">
						<div class="setting-content">
							{!! $setting->value !!}
						</div>
					</div>
				</div>
				
				<!-- Raw Content -->
				<div class="card mt-4">
					<div class="card-header">
						<h5 class="mb-0">Raw Content</h5>
					</div>
					<div class="card-body">
						<pre class="bg-light p-3 rounded"><code>{{ $setting->value }}</code></pre>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>

@endsection

@section('styles')
<style>
.setting-content {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: #333;
}

.setting-content h1, .setting-content h2, .setting-content h3,
.setting-content h4, .setting-content h5, .setting-content h6 {
    margin-top: 1.5em;
    margin-bottom: 0.5em;
    font-weight: 600;
}

.setting-content p {
    margin-bottom: 1em;
}

.setting-content ul, .setting-content ol {
    margin-bottom: 1em;
    padding-left: 2em;
}

.setting-content img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.setting-content blockquote {
    border-left: 4px solid #007bff;
    margin: 1em 0;
    padding-left: 1em;
    color: #666;
    font-style: italic;
}

.setting-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 1em 0;
}

.setting-content table th,
.setting-content table td {
    border: 1px solid #ddd;
    padding: 8px 12px;
    text-align: left;
}

.setting-content table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
</style>
@endsection