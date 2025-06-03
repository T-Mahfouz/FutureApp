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
			<div class="h3 mb-0">Settings Management</div>
			<div>
				<a href="{{ route('setting.create') }}" class="btn btn-primary mr-2">
					<i class="gd-plus"></i> Add Setting
				</a>
				<button type="button" class="btn btn-info" data-toggle="modal" data-target="#bulkEditModal">
					<i class="gd-pencil"></i> Bulk Edit
				</button>
			</div>
		</div>

		<!-- Filters -->
		<div class="card mb-3">
			<div class="card-body">
				<form method="GET" action="{{ route('setting.index') }}">
					<div class="row">
						<div class="col-md-3">
							<select name="city_id" class="form-control">
								<option value="">All Cities</option>
								@foreach($cities as $city)
									<option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
										{{ $city->name }}
									</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-3">
							<select name="type" class="form-control">
								<option value="">All Types</option>
								@foreach($settingTypes as $key => $type)
									<option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
										{{ $type }}
									</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-4">
							<input type="text" name="search" class="form-control" placeholder="Search settings..." value="{{ request('search') }}">
						</div>
						<div class="col-md-2">
							<button type="submit" class="btn btn-outline-primary btn-block">Filter</button>
						</div>
					</div>
				</form>
			</div>
		</div>

		<!-- Settings -->
		<div class="table-responsive-xl">
			<table class="table text-nowrap mb-0">
				<thead>
				<tr>
					<th class="font-weight-semi-bold border-top-0 py-2">#</th>
					<th class="font-weight-semi-bold border-top-0 py-2">City</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Setting Key</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Type</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Value Preview</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Last Updated</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
				</tr>
				</thead>
				<tbody>
				@forelse($settings as $setting)
				<tr>
					<td class="py-3">{{ $setting->id }}</td>
					<td class="py-3">
						<span class="badge badge-primary">{{ $setting->city->name ?? 'N/A' }}</span>
					</td>
					<td class="py-3">
						<strong>{{ $setting->key }}</strong>
					</td>
					<td class="py-3">
						@php
							$type = 'custom';
							foreach($settingTypes as $key => $label){
								if(str_starts_with($setting->key, $key)){
									$type = $label;
									break;
								}
							}
						@endphp
						<span class="badge badge-secondary">{{ $type }}</span>
					</td>
					<td class="py-3" style="max-width: 200px;">
						<div class="text-truncate">
							{{ Str::limit(strip_tags($setting->value), 50) }}
						</div>
					</td>
					<td class="py-3">{{ $setting->updated_at->diffForHumans() }}</td>
					<td class="py-3">
						<div class="position-relative">
							<a class="link-dark d-inline-block" href="{{ route('setting.show', $setting) }}" title="View">
								<i class="gd-eye icon-text"></i>
							</a>
							<a class="link-dark d-inline-block" href="{{ route('setting.edit', $setting) }}" title="Edit">
								<i class="gd-pencil icon-text"></i>
							</a>
							<a class="link-dark d-inline-block" href="#" onclick="if(confirm('Delete this setting?')){document.getElementById('delete-entity-{{ $setting->id }}').submit();return false;}" title="Delete">
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
					<td colspan="7" class="text-center">
						<strong>No settings found</strong><br>
						<a href="{{ route('setting.create') }}" class="btn btn-primary btn-sm mt-2">Create First Setting</a>
					</td>
				</tr>
				@endforelse

				</tbody>
			</table>
			
			{{ $settings->appends(request()->query())->links('components.pagination') }}
			
		</div>
		<!-- End Settings -->
	</div>
</div>

<!-- Bulk Edit Modal -->
<div class="modal fade" id="bulkEditModal" tabindex="-1" role="dialog" aria-labelledby="bulkEditModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="bulkEditModalLabel">Bulk Edit Settings</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form action="{{ route('setting.bulk-edit') }}" method="GET">
				<div class="modal-body">
					<div class="form-group">
						<label for="bulk_city_id">Select City</label>
						<select name="city_id" id="bulk_city_id" class="form-control" required>
							<option value="">Choose city...</option>
							@foreach($cities as $city)
								<option value="{{ $city->id }}">{{ $city->name }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Edit Settings</button>
				</div>
			</form>
		</div>
	</div>
</div>

@endsection