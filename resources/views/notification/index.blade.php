@extends('layouts.grain')

@section('title', 'Notifications')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">

	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item active" aria-current="page">Notifications</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">Notifications</div>
			<div>
				<a href="{{ route('notification.create') }}" class="btn btn-primary">
					<i class="gd-plus"></i> Add New
				</a>
				<a href="{{ route('notification.send-firebase') }}" class="btn btn-success ml-2">
					<i class="gd-rocket"></i> Send Firebase Only
				</a>
			</div>
		</div>

		<!-- Notifications -->
		<div class="table-responsive-xl">
			<table class="table text-nowrap mb-0">
				<thead>
				<tr>
					<th class="font-weight-semi-bold border-top-0 py-2">#</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Image</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Title</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Body</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Related To</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Created Date</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
				</tr>
				</thead>
				<tbody>
				@forelse($notifications as $notification)
				<tr>
					<td class="py-3">{{ $notification->id }}</td>
					<td class="py-3">
						@if($notification->image)
							<img src="{{ asset('storage/' . $notification->image->path) }}" alt="{{ $notification->title }}" class="rounded" width="40" height="40">
						@else
							<span class="avatar-placeholder bg-secondary text-white rounded d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
								<i class="gd-bell"></i>
							</span>
						@endif
					</td>
					<td class="align-middle py-3">
						<div>
							<strong>{{ Str::limit($notification->title, 30) }}</strong>
						</div>
					</td>
					<td class="py-3">
						<span class="text-muted">{{ Str::limit($notification->body, 50) }}</span>
					</td>
					<td class="py-3">
						@if($notification->service)
							<span class="badge badge-info">Service: {{ Str::limit($notification->service->name, 20) }}</span>
						@elseif($notification->news)
							<span class="badge badge-success">News: {{ Str::limit($notification->news->name, 20) }}</span>
						@else
							<span class="badge badge-secondary">General</span>
						@endif
					</td>
					<td class="py-3">{{ $notification->created_at ? $notification->created_at->diffForHumans() : '---' }}</td>
					<td class="py-3">
						<div class="position-relative">
							<a class="link-dark d-inline-block" href="{{ route('notification.show', $notification) }}" title="View Details">
								<i class="gd-eye icon-text"></i>
							</a>
							<a class="link-dark d-inline-block" href="{{ route('notification.edit', $notification) }}" title="Edit">
								<i class="gd-pencil icon-text"></i>
							</a>
							<a class="link-dark d-inline-block" href="#" onclick="if(confirm('Delete this notification? This action cannot be undone.')){document.getElementById('delete-entity-{{ $notification->id }}').submit();return false;}" title="Delete">
								<i class="gd-trash icon-text"></i>
							</a>
							<form id="delete-entity-{{ $notification->id }}" action="{{ route('notification.destroy', $notification) }}" method="POST">
								<input type="hidden" name="_method" value="DELETE">
								@csrf
							</form>
						</div>
					</td>
				</tr>
				@empty
				<tr>
					<td colspan="7" class="text-center">
						<strong>No records found</strong><br>
						<a href="{{ route('notification.create') }}" class="btn btn-primary btn-sm mt-2">Create First Notification</a>
					</td>
				</tr>
				@endforelse

				</tbody>
			</table>
			
			{{ $notifications->links('components.pagination') }}
			
		</div>
		<!-- End Notifications -->
	</div>
</div>
@endsection