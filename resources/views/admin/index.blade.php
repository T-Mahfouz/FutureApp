@extends('layouts.grain')

@section('title', 'Admins')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">

	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item active" aria-current="page">Admins</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">Admins</div>
			<a href="{{ route('admin.create') }}" class="btn btn-primary">
				Add new
			</a>
		</div>

		<!-- Admins -->
		<div class="table-responsive-xl">
			<table class="table text-nowrap mb-0">
				<thead>
				<tr>
					<th class="font-weight-semi-bold border-top-0 py-2">#</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Image</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Name</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Email</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Cities</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Created Date</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
				</tr>
				</thead>
				<tbody>
				@forelse($admins as $admin)
				<tr>
					<td class="py-3">{{ $admin->id }}</td>
					<td class="py-3">
						@if($admin->image)
							<img src="{{ asset('storage/' . $admin->image->path) }}" alt="{{ $admin->name }}" class="rounded-circle" width="40" height="40">
						@else
							<span class="avatar-placeholder">{{ substr($admin->name, 0, 1) }}</span>
						@endif
					</td>
					<td class="align-middle py-3">
						<div class="d-flex align-items-center">
							{{ $admin->name }}
						</div>
					</td>
					<td class="py-3">{{ $admin->email }}</td>
					<td class="py-3">
						@if($admin->cities->count() > 0)
							@foreach($admin->cities as $city)
								<span class="badge badge-secondary mr-1">{{ $city->name }}</span>
							@endforeach
						@else
							<span class="text-muted">No cities assigned</span>
						@endif
					</td>
					<td class="py-3">{{ $admin->created_at->diffForHumans() }}</td>
					<td class="py-3">
						<div class="position-relative">
							<a class="link-dark d-inline-block" href="{{ route('admin.edit', $admin) }}">
								<i class="gd-pencil icon-text"></i>
							</a>
							<a class="link-dark d-inline-block" href="#" onclick="if(confirm('Delete this record?')){document.getElementById('delete-entity-{{ $admin->id }}').submit();return false;}">
								<i class="gd-trash icon-text"></i>
							</a>
							<form id="delete-entity-{{ $admin->id }}" action="{{ route('admin.destroy', $admin) }}" method="POST">
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
					</td>
				</tr>
				@endforelse

				</tbody>
			</table>
			
			{{ $admins->links('components.pagination') }}
			
		</div>
		<!-- End Admins -->
	</div>
</div>
@endsection