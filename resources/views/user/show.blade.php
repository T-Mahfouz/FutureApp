@extends('layouts.grain')

@section('title', 'User Info')

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
				<li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
			<div class="d-flex align-items-center">
				@if($user->image && $user->image->path)
					<img src="{{ asset('storage/' . $user->image->path) }}" alt="{{ $user->name }}" class="rounded-circle mr-3" width="80" height="80" style="object-fit: cover;">
				@else
					<span class="avatar-placeholder bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mr-3" style="width: 80px; height: 80px; font-size: 24px;">
						{{ substr($user->name, 0, 1) }}
					</span>
				@endif
				<div>
					<h3 class="mb-0">{{ $user->name }}</h3>
					<small class="text-muted">{{ $user->email }}</small>
					@if($user->city)
						<div><span class="badge badge-primary mt-1">{{ $user->city->name }}</span></div>
					@endif
				</div>
			</div>
			<div>
				<a href="{{ route('user.edit', $user) }}" class="btn btn-primary">
					<i class="gd-pencil"></i> Edit User
				</a>
				@if($user->id != auth()->user()->id)
					@if($user->blocked)
						<form action="{{ route('user.unblock', $user) }}" method="POST" class="d-inline">
							@csrf
							<button type="submit" class="btn btn-success" onclick="return confirm('Unblock this user?');">
								<i class="gd-check"></i> Unblock
							</button>
						</form>
					@else
						<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#blockUserModal">
							<i class="gd-ban"></i> Block User
						</button>
					@endif
				@endif
			</div>
		</div>

		@if($user->blocked)
		<div class="alert alert-danger">
			<strong><i class="gd-ban"></i> This user is blocked.</strong>
			@if($user->block_reason)
				<div class="mt-1"><strong>Reason:</strong> {{ $user->block_reason }}</div>
			@endif
		</div>
		@endif

		<!-- Basic Information -->
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">Basic Information</h5>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-6">
						<strong>Name:</strong>
						<p class="text-muted">{{ $user->name }}</p>
					</div>
					<div class="col-md-6">
						<strong>Email:</strong>
						<p class="text-muted">{{ $user->email }}</p>
					</div>
					<div class="col-md-6">
						<strong>Phone:</strong>
						<p class="text-muted">{{ $user->phone ?? '—' }}</p>
					</div>
					<div class="col-md-6">
						<strong>City:</strong>
						<p class="text-muted">{{ $user->city->name ?? '—' }}</p>
					</div>
					<div class="col-md-6">
						<strong>Registered:</strong>
						<p class="text-muted">{{ $user->created_at ? $user->created_at->format('M d, Y \a\t g:i A') : '—' }}</p>
					</div>
					<div class="col-md-6">
						<strong>Verified:</strong>
						<p class="text-muted">
							@if($user->is_verified)
								<span class="badge badge-success">Yes</span>
							@else
								<span class="badge badge-secondary">No</span>
							@endif
						</p>
					</div>
				</div>
			</div>
		</div>

		<!-- Quick Stats -->
		<div class="row mb-4">
			<div class="col-md-4">
				<div class="card bg-info text-white">
					<div class="card-body text-center">
						<h4>{{ $ratings->total() }}</h4>
						<small>Ratings Given</small>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card bg-success text-white">
					<div class="card-body text-center">
						<h4>{{ $user->favorites()->count() }}</h4>
						<small>Favorites</small>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card bg-warning text-white">
					<div class="card-body text-center">
						<h4>{{ number_format($user->rates()->avg('rate') ?? 0, 1) }} ★</h4>
						<small>Average Rating Given</small>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Rating Analysis -->
<div class="card mb-3 mb-md-4">
	<div class="card-header d-flex justify-content-between align-items-center">
		<h5 class="mb-0">Rating Analysis ({{ $ratings->total() }} total)</h5>
	</div>
	<div class="card-body">
		@if($ratings->total() === 0)
			<p class="text-muted mb-0">This user hasn't rated any service yet.</p>
		@else
			<div class="table-responsive">
				<table class="table table-sm align-middle mb-0">
					<thead>
						<tr>
							<th>Service</th>
							<th>City</th>
							<th>Categories</th>
							<th>Rating</th>
							<th>Date</th>
						</tr>
					</thead>
					<tbody>
						@foreach($ratings as $rate)
						<tr>
							<td>
								@if($rate->service)
									<a href="{{ route('service.show', $rate->service) }}">{{ $rate->service->name }}</a>
								@else
									<span class="text-muted font-italic">Deleted service</span>
								@endif
							</td>
							<td>{{ $rate->service->city->name ?? '—' }}</td>
							<td>
								@if($rate->service && $rate->service->categories->count() > 0)
									@foreach($rate->service->categories as $category)
										<span class="badge badge-secondary mr-1">{{ $category->name }}</span>
									@endforeach
								@else
									<span class="text-muted">—</span>
								@endif
							</td>
							<td class="text-warning text-nowrap">
								@for($i = 1; $i <= 5; $i++){{ $i <= $rate->rate ? '★' : '☆' }}@endfor
								<span class="text-muted ml-1">({{ $rate->rate }}/5)</span>
							</td>
							<td class="text-nowrap">{{ $rate->created_at->format('M d, Y \a\t g:i A') }}</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			{{ $ratings->links('components.pagination') }}
		@endif
	</div>
</div>

@if(!$user->blocked && $user->id != auth()->user()->id)
<!-- Block User Modal -->
<div class="modal fade" id="blockUserModal" tabindex="-1" role="dialog" aria-labelledby="blockUserModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<form action="{{ route('user.block', $user) }}" method="POST">
			@csrf
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="blockUserModalLabel">Block {{ $user->name }}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="block_reason">Reason <span class="text-danger">*</span></label>
						<textarea class="form-control" id="block_reason" name="block_reason" rows="3" maxlength="255" required placeholder="Why are you blocking this user?"></textarea>
						<small class="form-text text-muted">Max 255 characters.</small>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-danger">Block User</button>
				</div>
			</div>
		</form>
	</div>
</div>
@endif

@endsection

@section('scripts')
<script>
$(function () {
    $('#blockUserModal').appendTo('body');
});
</script>
@endsection
