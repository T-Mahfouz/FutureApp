@extends('layouts.grain')

@section('title', 'Cities')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">

	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item active" aria-current="page">Cities</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">Cities</div>
			<a href="{{ route('city.create') }}" class="btn btn-primary">
				Add new
			</a>
		</div>

		<!-- Cities -->
		<div class="table-responsive-xl">
			<table class="table text-nowrap mb-0">
				<thead>
				<tr>
					<th class="font-weight-semi-bold border-top-0 py-2">#</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Image</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Name</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Services</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Categories</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Admins</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Created Date</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
				</tr>
				</thead>
				<tbody>
				@forelse($cities as $city)
				<tr>
					<td class="py-3">{{ $city->id }}</td>
					<td class="py-3">
						@if($city->image)
							<img src="{{ asset('storage/' . $city->image->path) }}" alt="{{ $city->name }}" class="rounded" width="40" height="40">
						@else
							<span class="avatar-placeholder bg-secondary text-white rounded d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
								{{ substr($city->name, 0, 1) }}
							</span>
						@endif
					</td>
					<td class="align-middle py-3">
						<div class="d-flex align-items-center">
							<div>
								<strong>{{ $city->name }}</strong>
								@if($city->config)
									<br><small class="text-muted">Firebase configured</small>
								@endif
							</div>
						</div>
					</td>
					<td class="py-3">
						<span class="badge badge-info">{{ $city->services_count }}</span>
					</td>
					<td class="py-3">
						<span class="badge badge-success">{{ $city->categories_count }}</span>
					</td>
					<td class="py-3">
						<span class="badge badge-warning">{{ $city->admins_count }}</span>
					</td>
					<td class="py-3">{{ $city->created_at ? $city->created_at->diffForHumans() : '---' }}</td>
					<td class="py-3">
						<div class="position-relative">
							<a class="link-dark d-inline-block" href="{{ route('city.show', $city) }}" title="View Details">
								<i class="gd-eye icon-text"></i>
							</a>
							<a class="link-dark d-inline-block" href="{{ route('city.edit', $city) }}" title="Edit">
								<i class="gd-pencil icon-text"></i>
							</a>
							<a class="link-dark d-inline-block" href="#" onclick="if(confirm('Delete this city? This action cannot be undone.')){document.getElementById('delete-entity-{{ $city->id }}').submit();return false;}" title="Delete">
								<i class="gd-trash icon-text"></i>
							</a>
							<form id="delete-entity-{{ $city->id }}" action="{{ route('city.destroy', $city) }}" method="POST">
								<input type="hidden" name="_method" value="DELETE">
								@csrf
							</form>
						</div>
					</td>
				</tr>
				@empty
				<tr>
					<td colspan="8" class="text-center">
						<strong>No records found</strong><br>
						<a href="{{ route('city.create') }}" class="btn btn-primary btn-sm mt-2">Create First City</a>
					</td>
				</tr>
				@endforelse

				</tbody>
			</table>
			
			{{ $cities->links('components.pagination') }}
			
		</div>
		<!-- End Cities -->
	</div>
</div>
@endsection