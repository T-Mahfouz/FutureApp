@extends('layouts.grain')

@section('title', 'Categories')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">

	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item active" aria-current="page">Categories</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">Categories</div>
			<a href="{{ route('category.create') }}" class="btn btn-primary">
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
					<form method="GET" action="{{ route('category.index') }}">
						<!-- First Row - Main Filters -->
						<div class="row">
							<div class="col-lg-3 col-md-4 col-sm-6 mb-3">
								<label for="search" class="form-label">Search by Name</label>
								<div class="position-relative">
									<input type="text" class="form-control pr-5" id="search" name="search" value="{{ request('search') }}" placeholder="Search categories...">
									<div class="position-absolute" style="right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
										<i class="gd-search text-muted"></i>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-md-4 col-sm-6 mb-3">
								<label for="description_search" class="form-label">Search by Description</label>
								<div class="position-relative">
									<input type="text" class="form-control pr-5" id="description_search" name="description_search" value="{{ request('description_search') }}" placeholder="Search descriptions...">
									<div class="position-absolute" style="right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
										<i class="gd-search text-muted"></i>
									</div>
								</div>
							</div>
							<div class="col-lg-2 col-md-4 col-sm-6 mb-3">
								<label for="city_id" class="form-label">City</label>
								<select class="form-control" id="city_id" name="city_id">
									<option value="">All Cities</option>
									@foreach($cities as $city)
										<option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="col-lg-3 col-md-4 col-sm-6 mb-3">
								<label for="parent_filter" class="form-label">Category Type</label>
								<select class="form-control" id="parent_filter" name="parent_filter">
									<option value="">All Types</option>
									<option value="main" {{ request('parent_filter') == 'main' ? 'selected' : '' }}>Main Categories</option>
									<option value="sub" {{ request('parent_filter') == 'sub' ? 'selected' : '' }}>Sub Categories</option>
									<optgroup label="Sub-categories of:">
										@foreach($parentCategories as $parent)
											<option value="{{ $parent->id }}" {{ request('parent_filter') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
										@endforeach
									</optgroup>
								</select>
							</div>
							<div class="col-lg-2 col-md-4 col-sm-6 mb-3">
								<label for="active" class="form-label">Status</label>
								<select class="form-control" id="active" name="active">
									<option value="">All Status</option>
									<option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
									<option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive</option>
								</select>
							</div>
							<div class="col-lg-2 col-md-4 col-sm-6 mb-3 d-flex align-items-end">
								<button type="submit" class="btn btn-primary btn-block">
									<i class="gd-search"></i> Search
								</button>
							</div>
						</div>

						<!-- Second Row - Date Filters -->
						<div class="row">
							<div class="col-lg-2 col-md-3 col-sm-6 mb-3">
								<label for="date_from" class="form-label">From Date</label>
								<input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
							</div>
							<div class="col-lg-2 col-md-3 col-sm-6 mb-3">
								<label for="date_to" class="form-label">To Date</label>
								<input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
							</div>
							<div class="col-lg-2 col-md-3 col-sm-6 mb-3">
								<label for="sort_by" class="form-label">Sort By</label>
								<select class="form-control" id="sort_by" name="sort_by">
									<option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Created Date</option>
									<option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
									<option value="services_count" {{ request('sort_by') == 'services_count' ? 'selected' : '' }}>Services Count</option>
									<option value="children_count" {{ request('sort_by') == 'children_count' ? 'selected' : '' }}>Sub-categories Count</option>
								</select>
							</div>
							<div class="col-lg-2 col-md-3 col-sm-6 mb-3">
								<label for="sort_direction" class="form-label">Direction</label>
								<select class="form-control" id="sort_direction" name="sort_direction">
									<option value="desc" {{ request('sort_direction') == 'desc' ? 'selected' : '' }}>Descending</option>
									<option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>Ascending</option>
								</select>
							</div>
							<div class="col-lg-2 col-md-6 col-sm-6 mb-3 d-flex align-items-end">
								<button type="submit" class="btn btn-success btn-block">
									<i class="gd-funnel"></i> Apply Filters
								</button>
							</div>
							<div class="col-lg-2 col-md-6 col-sm-6 mb-3 d-flex align-items-end">
								<a href="{{ route('category.index') }}" class="btn btn-outline-secondary btn-block">
									<i class="gd-reload"></i> Clear All
								</a>
							</div>
						</div>

						<!-- Active Filters Display -->
						@if(request()->hasAny(['search', 'city_id', 'parent_filter', 'active', 'date_from', 'date_to']))
						<div class="row">
							<div class="col-12">
								<div class="border-top pt-3">
									<small class="text-muted">Active filters:</small>
									<div class="mt-2">
										@if(request('search'))
											<span class="badge badge-primary mr-1 mb-1">Search: "{{ request('search') }}"</span>
										@endif
										@if(request('description_search'))
											<span class="badge badge-primary mr-1 mb-1">Description: "{{ request('description_search') }}"</span>
										@endif
										@if(request('city_id'))
											<span class="badge badge-info mr-1 mb-1">City: {{ $cities->find(request('city_id'))->name ?? 'Unknown' }}</span>
										@endif
										@if(request('parent_filter'))
											<span class="badge badge-secondary mr-1 mb-1">
												Type: 
												@if(request('parent_filter') == 'main')
													Main Categories
												@elseif(request('parent_filter') == 'sub')
													Sub Categories
												@else
													Sub of {{ $parentCategories->find(request('parent_filter'))->name ?? 'Unknown' }}
												@endif
											</span>
										@endif
										@if(request('active') !== null)
											<span class="badge badge-{{ request('active') == '1' ? 'success' : 'danger' }} mr-1 mb-1">
												{{ request('active') == '1' ? 'Active' : 'Inactive' }}
											</span>
										@endif
										@if(request('date_from'))
											<span class="badge badge-warning mr-1 mb-1">From: {{ request('date_from') }}</span>
										@endif
										@if(request('date_to'))
											<span class="badge badge-warning mr-1 mb-1">To: {{ request('date_to') }}</span>
										@endif
									</div>
								</div>
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
					Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }} results
					@if(request()->hasAny(['search', 'city_id', 'parent_filter', 'active', 'date_from', 'date_to']))
						<span class="badge badge-info ml-1">Filtered</span>
					@endif
				</small>
			</div>
			@if(request()->hasAny(['search', 'city_id', 'parent_filter', 'active', 'date_from', 'date_to']))
			<div>
				<a href="{{ route('category.index') }}" class="btn btn-sm btn-outline-secondary">
					<i class="gd-close"></i> Clear All Filters
				</a>
			</div>
			@endif
		</div>

		<!-- Categories -->
		<div class="table-responsive-xl">
			<table class="table text-nowrap mb-0">
				<thead>
				<tr>
					<th class="font-weight-semi-bold border-top-0 py-2">#</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Image</th>
					<th class="font-weight-semi-bold border-top-0 py-2">
						<a href="{{ route('category.index', array_merge(request()->query(), ['sort_by' => 'name', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
							Name
							@if(request('sort_by') == 'name')
								<i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
							@endif
						</a>
					</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Description</th>
					<th class="font-weight-semi-bold border-top-0 py-2">City</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Parent</th>
					<th class="font-weight-semi-bold border-top-0 py-2">
						<a href="{{ route('category.index', array_merge(request()->query(), ['sort_by' => 'services_count', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
							Services
							@if(request('sort_by') == 'services_count')
								<i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
							@endif
						</a>
					</th>
					<th class="font-weight-semi-bold border-top-0 py-2">
						<a href="{{ route('category.index', array_merge(request()->query(), ['sort_by' => 'children_count', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
							Sub-categories
							@if(request('sort_by') == 'children_count')
								<i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
							@endif
						</a>
					</th>
					<th class="font-weight-semi-bold border-top-0 py-2">
						<a href="{{ route('category.index', array_merge(request()->query(), ['sort_by' => 'created_at', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
							Created Date
							@if(request('sort_by') == 'created_at' || !request('sort_by'))
								<i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
							@endif
						</a>
					</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
				</tr>
				</thead>
				<tbody>
				@forelse($categories as $category)
				<tr>
					<td class="py-3">{{ $category->id }}</td>
					<td class="py-3">
						@if($category->image)
							<img src="{{ asset('storage/' . $category->image->path) }}" alt="{{ $category->name }}" class="rounded" width="40" height="40">
						@else
							<span class="avatar-placeholder bg-secondary text-white rounded d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
								{{ substr($category->name, 0, 1) }}
							</span>
						@endif
					</td>
					<td class="align-middle py-3">
						<div class="d-flex align-items-center">
							<div>
								<div class="d-flex align-items-center mb-1">
									<strong class="mr-2">{{ $category->name }}</strong>
									@if($category->active)
										<span class="badge badge-success badge-pill">Active</span>
									@else
										<span class="badge badge-danger badge-pill">Inactive</span>
									@endif
								</div>
								@if($category->parent_id)
									<small class="text-muted">Sub-category</small>
								@endif
							</div>
						</div>
					</td>
					<td class="py-3">
						@if($category->description)
							<span class="text-muted" title="{{ $category->description }}">{{ Str::limit($category->description, 50) }}</span>
						@else
							<span class="text-muted font-italic">No description</span>
						@endif
					</td>
					<td class="py-3">
						<span class="badge badge-primary">{{ $category->city->name ?? 'N/A' }}</span>
					</td>
					<td class="py-3">
						@if($category->parent)
							<span class="badge badge-secondary">{{ $category->parent->name }}</span>
						@else
							<span class="text-muted">Main Category</span>
						@endif
					</td>
					<td class="py-3">
						<span class="badge badge-info">{{ $category->services_count }}</span>
					</td>
					<td class="py-3">
						<span class="badge badge-success">{{ $category->children_count }}</span>
					</td>
					<td class="py-3">{{ $category->created_at ? $category->created_at->diffForHumans() : '---' }}</td>
					<td class="py-3">
						<div class="position-relative">
							<a class="link-dark d-inline-block mr-2" href="{{ route('category.show', $category) }}" title="View Details">
								<i class="gd-eye icon-text"></i>
							</a>
							<a class="link-dark d-inline-block mr-2" href="{{ route('category.edit', $category) }}" title="Edit">
								<i class="gd-pencil icon-text"></i>
							</a>
							<a class="d-inline-block mr-2 text-danger" href="#" onclick="if(confirm('{{ $category->active ? 'Deactivate' : 'Activate' }} this category?')){document.getElementById('toggle-status-{{ $category->id }}').submit();return false;}" title="{{ $category->active ? 'Deactivate' : 'Activate' }}">
								<i class="gd-reload icon-text"></i>
							</a>
							<a class="link-dark d-inline-block" href="#" onclick="confirmDelete({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->services_count }}, {{ $category->children_count }})" title="Delete">
								<i class="gd-trash icon-text text-danger"></i>
							</a>
							<form id="toggle-status-{{ $category->id }}" action="{{ route('category.toggle-status', $category) }}" method="POST" style="display: none;">
								@csrf
							</form>
							<form id="delete-form-{{ $category->id }}" action="{{ route('category.destroy', $category) }}" method="POST" style="display: none;">
								@csrf
								@method('DELETE')
							</form>
							<form id="force-delete-form-{{ $category->id }}" action="{{ route('category.destroy', $category) }}" method="POST" style="display: none;">
								@csrf
								@method('DELETE')
								<input type="hidden" name="force" value="true">
							</form>
						</div>
					</td>
				</tr>
				@empty
				<tr>
					<td colspan="10" class="text-center py-4"> <!-- NEW: Updated colspan to 10 -->
						@if(request()->hasAny(['search', 'description_search', 'city_id', 'parent_filter', 'active', 'date_from', 'date_to']))
							<strong>No categories found matching your filters</strong><br>
							<small class="text-muted">Try adjusting your search criteria</small><br>
							<a href="{{ route('category.index') }}" class="btn btn-outline-primary btn-sm mt-2">Clear Filters</a>
						@else
							<strong>No categories found</strong><br>
							<a href="{{ route('category.create') }}" class="btn btn-primary btn-sm mt-2">Create First Category</a>
						@endif
					</td>
				</tr>
				@endforelse

				</tbody>
			</table>
			
			{{ $categories->links('components.pagination') }}
			
		</div>
		<!-- End Categories -->
	</div>
</div>

<script>
function confirmDelete(categoryId, categoryName, servicesCount, childrenCount) {
    // If category has no related data, simple confirmation
    if (servicesCount == 0 && childrenCount == 0) {
        if (confirm('Are you sure you want to delete the category "' + categoryName + '"?\n\nThis action cannot be undone.')) {
            document.getElementById('delete-form-' + categoryId).submit();
        }
        return false;
    }
    
    // If category has related data, show detailed warning
    let message = '⚠️ WARNING: Category with Related Data ⚠️\n\n';
    message += 'The category "' + categoryName + '" contains:\n';
    
    if (servicesCount > 0) {
        message += '• ' + servicesCount + ' services\n';
    }
    if (childrenCount > 0) {
        message += '• ' + childrenCount + ' sub-categories\n';
    }
    
    message += '\nFirst deletion attempt will show you exactly what will be deleted.\n';
    message += 'You will then get a second chance to force delete everything.\n\n';
    message += 'Do you want to proceed with the deletion check?';
    
    if (confirm(message)) {
        document.getElementById('delete-form-' + categoryId).submit();
    }
    return false;
}

function forceDelete(categoryId, categoryName) {
    if (confirm('🚨 FINAL WARNING 🚨\n\n' +
               'This will PERMANENTLY DELETE the category "' + categoryName + '" and ALL related data.\n\n' +
               'This action CANNOT be undone!\n\n' +
               'Are you absolutely certain you want to proceed?')) {
        document.getElementById('force-delete-form-' + categoryId).submit();
    }
    return false;
}
</script>
@endsection