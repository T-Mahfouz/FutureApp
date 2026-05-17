@extends('layouts.grain')

@section('title', 'Users')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">

	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item active" aria-current="page">Users</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">Users</div>
			<a href="{{ route('user.create') }}" class="btn btn-primary">
				Add new
			</a>
		</div>

		<!-- Search and Filters -->
		<div class="card mb-4">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h6 class="mb-0">Search & Filters</h6>
				<button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#filterSection" aria-expanded="true" aria-controls="filterSection">
					<i class="gd-angle-down"></i> Collapse
				</button>
			</div>
			<div class="collapse show" id="filterSection">
				<div class="card-body">
					<form method="GET" action="{{ route('user.index') }}">
						<div class="row">
							<div class="col-lg-4 col-md-6 mb-3">
								<label for="search" class="form-label">Search Users</label>
								<div class="position-relative">
									<input type="text" class="form-control pr-5" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name, email, phone...">
									<div class="position-absolute" style="right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
										<i class="gd-search text-muted"></i>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-md-4 mb-3">
								<label for="city_id" class="form-label">City</label>
								<select class="form-control" id="city_id" name="city_id">
									<option value="">All Cities</option>
									@foreach($cities as $city)
										<option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="col-lg-2 col-md-4 mb-3">
								<label for="sort_by" class="form-label">Sort By</label>
								<select class="form-control" id="sort_by" name="sort_by">
									<option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Registration Date</option>
									<option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
									<option value="email" {{ request('sort_by') == 'email' ? 'selected' : '' }}>Email</option>
								</select>
							</div>
							<div class="col-lg-1 col-md-2 mb-3 d-flex align-items-end">
								<button type="submit" class="btn btn-primary btn-block">
									<i class="gd-search"></i>
								</button>
							</div>
							<div class="col-lg-2 col-md-2 mb-3 d-flex align-items-end">
								<a href="{{ route('user.index') }}" class="btn btn-outline-secondary btn-block">
									<i class="gd-reload"></i> Clear
								</a>
							</div>
						</div>

						@if(request()->hasAny(['search', 'city_id']))
						<div class="border-top pt-3">
							<small class="text-muted">Active filters:</small>
							<div class="mt-2">
								@if(request('search'))
									<span class="badge badge-primary mr-1 mb-1">Search: "{{ request('search') }}"</span>
								@endif
								@if(request('city_id'))
									<span class="badge badge-info mr-1 mb-1">City: {{ $cities->find(request('city_id'))->name ?? 'Unknown' }}</span>
								@endif
							</div>
						</div>
						@endif
					</form>
				</div>
			</div>
		</div>
		<!-- End Search and Filters -->

		<!-- Results Info -->
		<div class="d-flex justify-content-between align-items-center mb-3">
			<div>
				<small class="text-muted">
					Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
					@if(request()->hasAny(['search', 'city_id']))
						<span class="badge badge-info ml-1">Filtered</span>
					@endif
				</small>
			</div>
			@if(request()->hasAny(['search', 'city_id']))
			<div>
				<a href="{{ route('user.index') }}" class="btn btn-sm btn-outline-secondary">
					<i class="gd-close"></i> Clear All Filters
				</a>
			</div>
			@endif
		</div>

		<!-- Users -->
		<div class="table-responsive-xl">
			<table class="table text-nowrap mb-0">
				<thead>
				<tr>
					<th class="font-weight-semi-bold border-top-0 py-2">#</th>
					<!-- NEW: Profile Image column -->
					<th class="font-weight-semi-bold border-top-0 py-2">Image</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Name</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Email</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Phone</th>
					<th class="font-weight-semi-bold border-top-0 py-2">City</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Registration Date</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
				</tr>
				</thead>
				<tbody>
				@forelse($users as $user)
				<tr>
					<td class="py-3">{{ $user->id }}</td>
					<!-- NEW: Profile Image column content -->
					<td class="py-3">
						@if($user->image)
							<img src="{{ asset('storage/' . $user->image->path) }}" alt="{{ $user->name }}" class="rounded-circle" width="40" height="40">
						@else
							<span class="avatar-placeholder bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
								{{ substr($user->name, 0, 1) }}
							</span>
						@endif
					</td>
					<td class="align-middle py-3">
						<strong>{{ $user->name }}</strong>
					</td>
					<td class="py-3">{{ $user->email }}</td>
					<td class="py-3">{{ $user->phone ?? '---' }}</td>
					<td class="py-3">
						@if($user->city)
							<span class="badge badge-primary">{{ $user->city->name }}</span>
						@else
							<span class="text-muted">---</span>
						@endif
					</td>
					<td class="py-3">{{ $user->created_at->diffForHumans() }}</td>
					<td class="py-3">
						<div class="position-relative">
							<a class="link-dark d-inline-block mr-2" href="{{ route('user.show', $user) }}" title="View User Info">
								<i class="gd-eye icon-text"></i>
							</a>
							<a class="link-dark d-inline-block mr-2" href="{{ route('user.edit', $user) }}" title="Edit User">
								<i class="gd-pencil icon-text"></i>
							</a>
							@if($user->id != auth()->user()->id)
							<a class="link-dark d-inline-block" href="#" onclick="if(confirm('Delete this user? This action cannot be undone.')){document.getElementById('delete-entity-{{ $user->id }}').submit();return false;}" title="Delete User">
								<i class="gd-trash icon-text text-danger"></i>
							</a>
							<form id="delete-entity-{{ $user->id }}" action="{{ route('user.destroy', $user) }}" method="POST" style="display: none;">
								<input type="hidden" name="_method" value="DELETE">
								@csrf
							</form>
							@endif
						</div>
					</td>
				</tr>
				@empty
				<tr>
					<td colspan="8" class="text-center py-4"> <!-- NEW: Updated colspan to 8 -->
						<strong>No users found</strong><br>
						<a href="{{ route('user.create') }}" class="btn btn-primary btn-sm mt-2">Create First User</a>
					</td>
				</tr>
				@endforelse

				</tbody>
			</table>
			
			{{ $users->links('components.pagination') }}
			
		</div>
		<!-- End Users -->
	</div>
</div>
@endsection