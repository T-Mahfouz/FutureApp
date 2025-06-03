@extends('layouts.grain')

@section('title', 'Category Details')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">
	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('category.index') }}">Categories</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
			<div class="d-flex align-items-center">
				@if($category->image)
					<img src="{{ asset('storage/' . $category->image->path) }}" alt="{{ $category->name }}" class="rounded mr-3" width="60" height="60">
				@endif
				<div>
					<h3 class="mb-0">{{ $category->name }}</h3>
					<small class="text-muted">Created {{ $category->created_at ? $category->created_at->format('M d, Y') : 'Unknown' }}</small>
					@if($category->parent)
						<br><span class="badge badge-secondary">Sub-category of: {{ $category->parent->name }}</span>
					@else
						<br><span class="badge badge-primary">Main Category</span>
					@endif
					<br>
					@if($category->active)
						<span class="badge badge-success">Active</span>
					@else
						<span class="badge badge-danger">Inactive</span>
					@endif
				</div>
			</div>
			<div>
				<a href="{{ route('category.edit', $category) }}" class="btn btn-primary">
					<i class="gd-pencil"></i> Edit Category
				</a>
				<form class="d-inline" action="{{ route('category.toggle-status', $category) }}" method="POST" onsubmit="return confirm('{{ $category->active ? 'Deactivate' : 'Activate' }} this category?')">
					@csrf
					<button type="submit" class="btn btn-{{ $category->active ? 'warning' : 'success' }}">
						<i class="gd-{{ $category->active ? 'power-off' : 'power' }}"></i> {{ $category->active ? 'Deactivate' : 'Activate' }}
					</button>
				</form>
			</div>
		</div>

		<!-- Category Info -->
		<div class="row mb-4">
			<div class="col-md-6">
				<div class="card">
					<div class="card-header">
						<h5 class="mb-0">Category Information</h5>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-sm-4"><strong>Status:</strong></div>
							<div class="col-sm-8">
								@if($category->active)
									<span class="badge badge-success">Active</span>
								@else
									<span class="badge badge-danger">Inactive</span>
								@endif
							</div>
						</div>
						<hr>
						<div class="row">
							<div class="col-sm-4"><strong>City:</strong></div>
							<div class="col-sm-8">{{ $category->city->name ?? 'N/A' }}</div>
						</div>
						<hr>
						<div class="row">
							<div class="col-sm-4"><strong>Type:</strong></div>
							<div class="col-sm-8">
								@if($category->parent)
									Sub-category of "{{ $category->parent->name }}"
								@else
									Main Category
								@endif
							</div>
						</div>
						<hr>
						<div class="row">
							<div class="col-sm-4"><strong>Created:</strong></div>
							<div class="col-sm-8">{{ $category->created_at ? $category->created_at->format('M d, Y \a\t H:i') : 'Unknown' }}</div>
						</div>
						@if($category->updated_at && $category->updated_at != $category->created_at)
						<hr>
						<div class="row">
							<div class="col-sm-4"><strong>Last Updated:</strong></div>
							<div class="col-sm-8">{{ $category->updated_at->format('M d, Y \a\t H:i') }}</div>
						</div>
						@endif
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<!-- Category Statistics -->
				<div class="row">
					<div class="col-md-6 mb-3">
						<div class="card bg-info text-white">
							<div class="card-body text-center">
								<h4>{{ $category->services()->count() }}</h4>
								<small>Services</small>
							</div>
						</div>
					</div>
					<div class="col-md-6 mb-3">
						<div class="card bg-success text-white">
							<div class="card-body text-center">
								<h4>{{ $category->children()->count() }}</h4>
								<small>Sub-categories</small>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Quick Actions -->
				<div class="card">
					<div class="card-header">
						<h6 class="mb-0">Quick Actions</h6>
					</div>
					<div class="card-body">
						<div class="d-grid gap-2">
							<a href="{{ route('category.edit', $category) }}" class="btn btn-outline-primary btn-sm">
								<i class="gd-pencil"></i> Edit Category
							</a>
							<a href="{{ route('category.index', ['parent_filter' => $category->id]) }}" class="btn btn-outline-info btn-sm">
								<i class="gd-eye"></i> View Sub-categories ({{ $category->children()->count() }})
							</a>
							<a href="{{ route('service.index', ['category_id' => $category->id]) }}" class="btn btn-outline-success btn-sm">
								<i class="gd-layers"></i> View Services ({{ $category->services()->count() }})
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Sub-categories -->
		@if($category->children->count() > 0)
		<div class="card mb-4">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0">Sub-categories ({{ $category->children->count() }})</h5>
				<a href="{{ route('category.index', ['parent_filter' => $category->id]) }}" class="btn btn-sm btn-outline-primary">View All</a>
			</div>
			<div class="card-body">
				<div class="row">
					@foreach($category->children as $child)
					<div class="col-md-6 mb-3">
						<div class="d-flex align-items-center p-2 border rounded">
							@if($child->image)
								<img src="{{ asset('storage/' . $child->image->path) }}" alt="{{ $child->name }}" class="rounded mr-3" width="40" height="40">
							@else
								<span class="avatar-placeholder mr-3 bg-secondary text-white rounded d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">{{ substr($child->name, 0, 1) }}</span>
							@endif
							<div class="flex-grow-1">
								<strong>{{ $child->name }}</strong>
								<br><small class="text-muted">{{ $child->services()->count() }} services</small>
								@if(!$child->active)
									<br><span class="badge badge-danger badge-sm">Inactive</span>
								@endif
							</div>
							<div>
								<a href="{{ route('category.show', $child) }}" class="btn btn-sm btn-outline-primary">
									<i class="gd-eye"></i>
								</a>
							</div>
						</div>
					</div>
					@endforeach
				</div>
			</div>
		</div>
		@endif

		<!-- Recent Services -->
		@if($category->services->count() > 0)
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0">Services in this Category ({{ $category->services->count() }} total)</h5>
				<div>
					<small class="text-muted mr-3">Showing latest 5</small>
					<a href="{{ route('service.index', ['category_id' => $category->id]) }}" class="btn btn-sm btn-outline-primary">View All</a>
				</div>
			</div>
			<div class="card-body">
				@foreach($category->services()->latest()->take(5)->get() as $service)
				<div class="d-flex align-items-center mb-3 p-2 border rounded">
					@if($service->image)
						<img src="{{ asset('storage/' . $service->image->path) }}" alt="{{ $service->name }}" class="rounded mr-3" width="40" height="40">
					@else
						<span class="avatar-placeholder mr-3 bg-secondary text-white rounded d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">{{ substr($service->name, 0, 1) }}</span>
					@endif
					<div class="flex-grow-1">
						<strong>{{ $service->name }}</strong>
						<br><small class="text-muted">{{ Str::limit($service->brief_description, 60) }}</small>
						@if(!$service->valid)
							<br><span class="badge badge-warning badge-sm">Pending Approval</span>
						@endif
					</div>
					<div class="text-right">
						<small class="text-muted">{{ $service->created_at->diffForHumans() }}</small>
						<br>
						<a href="{{ route('service.show', $service) }}" class="btn btn-sm btn-outline-primary">
							<i class="gd-eye"></i>
						</a>
					</div>
				</div>
				@endforeach
			</div>
		</div>
		@else
		<div class="card">
			<div class="card-body text-center">
				<i class="gd-layers" style="font-size: 48px; color: #ccc;"></i>
				<h5 class="mt-3">No Services Yet</h5>
				<p class="text-muted">This category doesn't have any services assigned to it yet.</p>
				<a href="{{ route('service.create') }}" class="btn btn-primary">
					<i class="gd-plus"></i> Add First Service
				</a>
			</div>
		</div>
		@endif

	</div>
</div>
@endsection