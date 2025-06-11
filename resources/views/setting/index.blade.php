@extends('layouts.grain')

@section('title', 'Settings')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">

	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item active" aria-current="page">Settings</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">Settings</div>
			<a href="{{ route('setting.create') }}" class="btn btn-primary">
				Add new
			</a>
		</div>

		<!-- Settings -->
		<div class="table-responsive-xl">
			<table class="table text-nowrap mb-0">
				<thead>
				<tr>
					<th class="font-weight-semi-bold border-top-0 py-2">#</th>
					<th class="font-weight-semi-bold border-top-0 py-2">City</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Key</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Value</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Created Date</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
				</tr>
				</thead>
				<tbody>
				@forelse($settings as $setting)
				<tr>
					<td class="py-3">{{ $setting->id }}</td>
					<td class="py-3">
						<div class="d-flex align-items-center">
							<div>
								<strong>{{ $setting->city->name ?? 'No City' }}</strong>
							</div>
						</div>
					</td>
					<td class="align-middle py-3">
						<div class="d-flex align-items-center">
							<div>
								<strong>{{ $setting->key }}</strong>
							</div>
						</div>
					</td>
					<td class="py-3">
						<div style="max-width: 300px;">
							{!! Str::limit(strip_tags($setting->value), 100) !!}
						</div>
					</td>
					<td class="py-3">{{ $setting->created_at ? $setting->created_at->diffForHumans() : '---' }}</td>
					<td class="py-3">
						<div class="position-relative">
							<a class="link-dark d-inline-block" href="{{ route('setting.show', $setting) }}" title="View Details">
								<i class="gd-eye icon-text"></i>
							</a>
							<a class="link-dark d-inline-block" href="{{ route('setting.edit', $setting) }}" title="Edit">
								<i class="gd-pencil icon-text"></i>
							</a>
							<a class="link-dark d-inline-block" href="#" onclick="if(confirm('Delete this setting? This action cannot be undone.')){document.getElementById('delete-entity-{{ $setting->id }}').submit();return false;}" title="Delete">
								<i class="gd-trash icon-text"></i>
							</a>
							<form id="delete-entity-{{ $setting->id }}" action="{{ route('setting.destroy', $setting) }}" method="POST">
								<input type="hidden" name="_method" value="DELETE">
								@csrf
							</form>
						</div>
					</td>
				</tr>
				@empty
				<tr>
					<td colspan="6" class="text-center">
						<strong>No records found</strong><br>
						<a href="{{ route('setting.create') }}" class="btn btn-primary btn-sm mt-2">Create First Setting</a>
					</td>
				</tr>
				@endforelse

				</tbody>
			</table>
			
			{{ $settings->links('components.pagination') }}
			
		</div>
		<!-- End Settings -->
	</div>
</div>
@endsection