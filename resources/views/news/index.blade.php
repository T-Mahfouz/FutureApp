@extends('layouts.grain')

@section('title', 'News')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">

	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item active" aria-current="page">News</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">News</div>
			<a href="{{ route('news.create') }}" class="btn btn-primary">
				Add new
			</a>
		</div>

		<!-- Quick Filters -->
		<div class="mb-3">
			<div class="btn-group" role="group" aria-label="Quick filters">
				<a href="{{ route('news.index') }}" class="btn btn-outline-secondary {{ !request()->hasAny(['search', 'city_id', 'date_from', 'date_to', 'has_image']) ? 'active' : '' }}">
					All News
				</a>
				<a href="{{ request()->fullUrlWithQuery(['has_image' => '1']) }}" class="btn btn-outline-secondary {{ request('has_image') == '1' ? 'active' : '' }}">
					With Images
				</a>
				<a href="{{ request()->fullUrlWithQuery(['date_from' => now()->subWeek()->format('Y-m-d')]) }}" class="btn btn-outline-secondary {{ request('date_from') == now()->subWeek()->format('Y-m-d') ? 'active' : '' }}">
					This Week
				</a>
				<a href="{{ request()->fullUrlWithQuery(['date_from' => now()->subMonth()->format('Y-m-d')]) }}" class="btn btn-outline-secondary {{ request('date_from') == now()->subMonth()->format('Y-m-d') ? 'active' : '' }}">
					This Month
				</a>
			</div>
		</div>

		<!-- Search and Filter Section -->
		<div class="card mb-4">
			<div class="card-body">
				<form method="GET" action="{{ route('news.index') }}" class="row">
					<!-- Search -->
					<div class="col-md-3 mb-3">
						<label for="search" class="form-label">Search</label>
						<input type="text" class="form-control" id="search" name="search" 
							   value="{{ request('search') }}" placeholder="Search news title, description, or city...">
					</div>
					
					<!-- City Filter -->
					<div class="col-md-2 mb-3">
						<label for="city_id" class="form-label">City</label>
						<select class="form-control" id="city_id" name="city_id">
							<option value="">All Cities</option>
							@foreach($cities as $city)
								<option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
									{{ $city->name }}
								</option>
							@endforeach
						</select>
					</div>
					
					<!-- Date From -->
					<div class="col-md-2 mb-3">
						<label for="date_from" class="form-label">Date From</label>
						<input type="date" class="form-control" id="date_from" name="date_from" 
							   value="{{ request('date_from') }}">
					</div>
					
					<!-- Date To -->
					<div class="col-md-2 mb-3">
						<label for="date_to" class="form-label">Date To</label>
						<input type="date" class="form-control" id="date_to" name="date_to" 
							   value="{{ request('date_to') }}">
					</div>
					
					<!-- Has Image Filter -->
					<div class="col-md-2 mb-3">
						<label for="has_image" class="form-label">Has Image</label>
						<select class="form-control" id="has_image" name="has_image">
							<option value="">All</option>
							<option value="1" {{ request('has_image') == '1' ? 'selected' : '' }}>With Image</option>
							<option value="0" {{ request('has_image') == '0' ? 'selected' : '' }}>Without Image</option>
						</select>
					</div>
					
					<!-- Sort Options -->
					<div class="col-md-1 mb-3">
						<label for="sort_by" class="form-label">Sort By</label>
						<select class="form-control" id="sort_by" name="sort_by">
							<option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date</option>
							<option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
							<option value="updated_at" {{ request('sort_by') == 'updated_at' ? 'selected' : '' }}>Updated</option>
						</select>
						<select class="form-control mt-1" name="sort_order">
							<option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Desc</option>
							<option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Asc</option>
						</select>
					</div>
					
					<!-- Filter Actions -->
					<div class="col-12">
						<button type="submit" class="btn btn-primary">
							<i class="gd-search"></i> Filter
						</button>
						<a href="{{ route('news.index') }}" class="btn btn-secondary ml-2">
							<i class="gd-reload"></i> Clear
						</a>
					</div>
				</form>
			</div>
		</div>

		<!-- Results Summary -->
		@if(request()->hasAny(['search', 'city_id', 'date_from', 'date_to', 'has_image']))
		<div class="alert alert-info">
			<strong>{{ $news->total() }}</strong> results found
			@if(request('search'))
				for "<strong>{{ request('search') }}</strong>"
			@endif
			@if(request('city_id'))
				in city "<strong>{{ $cities->where('id', request('city_id'))->first()->name ?? 'Unknown' }}</strong>"
			@endif
			@if(request('date_from') || request('date_to'))
				between 
				<strong>{{ request('date_from') ?: 'beginning' }}</strong> 
				and 
				<strong>{{ request('date_to') ?: 'now' }}</strong>
			@endif
		</div>
		@endif

		<!-- News -->
		<div class="table-responsive-xl">
			<table class="table text-nowrap mb-0">
				<thead>
				<tr>
					<th class="font-weight-semi-bold border-top-0 py-2">
						<a href="{{ request()->fullUrlWithQuery(['sort_by' => 'id', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
							#
							@if(request('sort_by') == 'id')
								<i class="gd-angle-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
							@endif
						</a>
					</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Image</th>
					<th class="font-weight-semi-bold border-top-0 py-2">
						<a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
							Title
							@if(request('sort_by') == 'name')
								<i class="gd-angle-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
							@endif
						</a>
					</th>
					<th class="font-weight-semi-bold border-top-0 py-2">City</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Description Preview</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Additional Images</th>
					<th class="font-weight-semi-bold border-top-0 py-2">
						<a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
							Created Date
							@if(request('sort_by') == 'created_at' || !request('sort_by'))
								<i class="gd-angle-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
							@endif
						</a>
					</th>
					<th class="font-weight-semi-bold border-top-0 py-2">Actions</th>
				</tr>
				</thead>
				<tbody>
				@forelse($news as $newsItem)
				<tr>
					<td class="py-3">{{ $newsItem->id }}</td>
					<td class="py-3">
						@if($newsItem->image)
							<img src="{{ asset('storage/' . $newsItem->image->path) }}" alt="{{ $newsItem->name }}" class="rounded" width="40" height="40">
						@else
							<span class="avatar-placeholder bg-secondary text-white rounded d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
								{{ substr($newsItem->name, 0, 1) }}
							</span>
						@endif
					</td>
					<td class="align-middle py-3">
						<div class="d-flex align-items-center">
							<div>
								<strong>{{ $newsItem->name }}</strong>
								@if(request('search') && stripos($newsItem->name, request('search')) !== false)
									<i class="gd-search text-primary ml-1" title="Matched in title"></i>
								@endif
							</div>
						</div>
					</td>
					<td class="py-3">
						@if($newsItem->city)
							<span class="badge badge-primary">{{ $newsItem->city->name }}</span>
							@if(request('search') && stripos($newsItem->city->name, request('search')) !== false)
								<i class="gd-search text-primary ml-1" title="Matched in city"></i>
							@endif
						@else
							<span class="text-muted">No City</span>
						@endif
					</td>
					<td class="py-3">
						<span class="text-muted">{{ Str::limit(strip_tags($newsItem->description), 60) }}</span>
						@if(request('search') && stripos($newsItem->description, request('search')) !== false)
							<i class="gd-search text-primary ml-1" title="Matched in description"></i>
						@endif
					</td>
					<td class="py-3">
						@if($newsItem->images->count() > 0)
							<span class="badge badge-info">{{ $newsItem->images->count() }} images</span>
						@else
							<span class="text-muted">No additional images</span>
						@endif
					</td>
					<td class="py-3">{{ $newsItem->created_at ? $newsItem->created_at->diffForHumans() : '---' }}</td>
					<td class="py-3">
						<div class="position-relative">
							<a class="link-dark d-inline-block" href="{{ route('news.show', $newsItem) }}" title="View Details">
								<i class="gd-eye icon-text"></i>
							</a>
							<a class="link-dark d-inline-block" href="{{ route('news.edit', $newsItem) }}" title="Edit">
								<i class="gd-pencil icon-text"></i>
							</a>
							<a class="link-dark d-inline-block" href="#" onclick="if(confirm('Delete this news? This action cannot be undone.')){document.getElementById('delete-entity-{{ $newsItem->id }}').submit();return false;}" title="Delete">
								<i class="gd-trash icon-text"></i>
							</a>
							<form id="delete-entity-{{ $newsItem->id }}" action="{{ route('news.destroy', $newsItem) }}" method="POST">
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
						<a href="{{ route('news.create') }}" class="btn btn-primary btn-sm mt-2">Create First News</a>
					</td>
				</tr>
				@endforelse

				</tbody>
			</table>
			
			{{ $news->links('components.pagination') }}
			
		</div>
		<!-- End News -->
	</div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form on filter change
    const filterSelects = document.querySelectorAll('#city_id, #has_image, #sort_by, select[name="sort_order"]');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
    
    // Clear individual filters
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('clear-filter')) {
            const filterName = e.target.dataset.filter;
            const input = document.querySelector(`[name="${filterName}"]`);
            if (input) {
                input.value = '';
                input.closest('form').submit();
            }
        }
    });
    
    // Highlight search terms
    const searchTerm = '{{ request("search") }}';
    if (searchTerm) {
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        document.querySelectorAll('td').forEach(cell => {
            if (cell.textContent.toLowerCase().includes(searchTerm.toLowerCase())) {
                cell.innerHTML = cell.innerHTML.replace(regex, '<mark>$1</mark>');
            }
        });
    }
});
</script>
@endpush
@endsection