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