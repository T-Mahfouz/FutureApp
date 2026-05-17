@extends('layouts.grain')

@section('title', 'Blocked Users')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">
	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('user.index') }}">Users</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">Blocked</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">Blocked Users ({{ $users->total() }})</div>
			<a href="{{ route('user.index') }}" class="btn btn-outline-secondary">
				<i class="gd-arrow-left"></i> All Users
			</a>
		</div>

		<!-- Search -->
		<form method="GET" action="{{ route('user.blocked') }}" class="mb-3">
			<div class="row">
				<div class="col-md-6 mb-2">
					<input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by name, email, phone, or reason...">
				</div>
				<div class="col-md-3 mb-2">
					<button type="submit" class="btn btn-primary btn-block">
						<i class="gd-search"></i> Search
					</button>
				</div>
				@if(request('search'))
				<div class="col-md-3 mb-2">
					<a href="{{ route('user.blocked') }}" class="btn btn-outline-secondary btn-block">
						<i class="gd-close"></i> Clear
					</a>
				</div>
				@endif
			</div>
		</form>

		<div class="table-responsive-xl">
			<table class="table text-nowrap mb-0">
				<thead>
				<tr>
					<th class="font-weight-semi-bold border-top-0 py-2">#</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Image</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Name</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Email</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Phone</th>
					<th class="font-weight-semi-bold border-top-0 py-2 text-wrap" style="min-width: 250px;">Block Reason</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Blocked At</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
				</tr>
				</thead>
				<tbody>
				@forelse($users as $user)
				<tr>
					<td class="py-3">{{ $user->id }}</td>
					<td class="py-3">
						@if($user->image && $user->image->path)
							<img src="{{ asset('storage/' . $user->image->path) }}" alt="{{ $user->name }}" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
						@else
							<span class="avatar-placeholder bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
								{{ substr($user->name, 0, 1) }}
							</span>
						@endif
					</td>
					<td class="align-middle py-3">
						<a href="{{ route('user.show', $user) }}"><strong>{{ $user->name }}</strong></a>
					</td>
					<td class="py-3">{{ $user->email }}</td>
					<td class="py-3">{{ $user->phone ?? '---' }}</td>
					<td class="py-3 text-wrap">{{ $user->block_reason ?? '—' }}</td>
					<td class="py-3">{{ $user->updated_at ? $user->updated_at->format('M d, Y \a\t g:i A') : '—' }}</td>
					<td class="py-3">
						<form action="{{ route('user.unblock', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Unblock this user?');">
							@csrf
							<button type="submit" class="btn btn-sm btn-success" title="Unblock">
								<i class="gd-check"></i> Unblock
							</button>
						</form>
					</td>
				</tr>
				@empty
				<tr>
					<td colspan="8" class="text-center py-4">
						<strong>No blocked users.</strong>
					</td>
				</tr>
				@endforelse
				</tbody>
			</table>

			{{ $users->links('components.pagination') }}
		</div>
	</div>
</div>
@endsection
