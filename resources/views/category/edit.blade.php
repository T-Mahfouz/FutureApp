@extends('layouts.grain')

@section('title', 'Dashboard')

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
				@if($category->id)
					<li class="breadcrumb-item">
						<a href="{{ route('category.show', $category) }}">{{ $category->name }}</a>
					</li>
					<li class="breadcrumb-item active" aria-current="page">Edit</li>
				@else
					<li class="breadcrumb-item active" aria-current="page">Create New Category</li>
				@endif
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
			<div>
				<div class="h3 mb-0">{{ $category->id ? 'Edit' : 'Create New' }} Category</div>
				@if($category->id)
					<small class="text-muted">
						Last updated: {{ $category->updated_at ? $category->updated_at->diffForHumans() : 'Never' }}
					</small>
				@endif
			</div>
			@if($category->id)
			<div>
				<a href="{{ route('category.show', $category) }}" class="btn btn-outline-secondary">
					<i class="gd-eye"></i> View Details
				</a>
			</div>
			@endif
		</div>

		<!-- Form -->
		<div>
			<form method="post" action="{{ $category->id ? route('category.update', $category) : route('category.store') }}" enctype="multipart/form-data">
                @if($category->id)
				<input type="hidden" name="_method" value="patch">
                @endif
				@csrf
				
				<div class="row">
					<!-- Main Form -->
					<div class="col-lg-8">
						<div class="card">
							<div class="card-header">
								<h6 class="mb-0">Category Information</h6>
							</div>
							<div class="card-body">
								<div class="form-row">
									<div class="form-group col-12">
										<label for="name">Category Name <span class="text-danger">*</span></label>
										<input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name', $category->name) }}" id="name" name="name" placeholder="Enter category name" required>
										@if($errors->has('name'))
											<div class="invalid-feedback">{{ $errors->first('name') }}</div>
										@endif
									</div>
								</div>
								
								<div class="form-row">
									<div class="form-group col-12">
										<label for="description">Description</label>
										<textarea class="form-control{{ $errors->has('description') ? ' is-invalid' : '' }}" id="description" name="description" rows="4" placeholder="Enter category description (optional)">{{ old('description', $category->description) }}</textarea>
										@if($errors->has('description'))
											<div class="invalid-feedback">{{ $errors->first('description') }}</div>
										@endif
										<small class="form-text text-muted">A brief description of what this category includes (max 1000 characters)</small>
									</div>
								</div>

								<div class="form-row">
									<div class="form-group col-12 col-md-6">
										<label for="city_id">City <span class="text-danger">*</span></label>
										<select class="form-control{{ $errors->has('city_id') ? ' is-invalid' : '' }}" id="city_id" name="city_id" required>
											<option value="">Select City</option>
											@foreach($cities as $city)
												<option value="{{ $city->id }}" {{ old('city_id', $category->city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
											@endforeach
										</select>
										@if($errors->has('city_id'))
											<div class="invalid-feedback">{{ $errors->first('city_id') }}</div>
										@endif
									</div>
									<div class="form-group col-12 col-md-6">
										<label for="parent_id">Parent Category</label>
										<select class="form-control{{ $errors->has('parent_id') ? ' is-invalid' : '' }}" id="parent_id" name="parent_id">
											<option value="">Main Category (No Parent)</option>
											@foreach($parentCategories as $parentCategory)
												<option value="{{ $parentCategory->id }}" {{ old('parent_id', $category->parent_id) == $parentCategory->id ? 'selected' : '' }}>{{ $parentCategory->name }}</option>
											@endforeach
										</select>
										@if($errors->has('parent_id'))
											<div class="invalid-feedback">{{ $errors->first('parent_id') }}</div>
										@endif
										<small class="form-text text-muted">Leave empty to make this a main category</small>
									</div>
								</div>

								<div class="form-row">
									<div class="form-group col-12">
										<label for="image">Category Image</label>
										<input type="file" class="form-control{{ $errors->has('image') ? ' is-invalid' : '' }}" id="image" name="image" accept="image/*">
										@if($errors->has('image'))
											<div class="invalid-feedback">{{ $errors->first('image') }}</div>
										@endif
										<small class="form-text text-muted">Upload an image to represent this category (JPEG, PNG, JPG, GIF - Max: 2MB)</small>
									</div>
								</div>

								<div class="form-row">
									<div class="form-group col-12">
										<div class="custom-control custom-checkbox">
											<input type="hidden" name="active" value="0">
											<input type="checkbox" class="custom-control-input" id="active" name="active" value="1" {{ old('active', $category->active ?? true) ? 'checked' : '' }}>
											<label class="custom-control-label" for="active">
												<strong>Category is Active</strong>
											</label>
											<small class="form-text text-muted">Inactive categories will be hidden from users and won't appear in the app</small>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Sidebar -->
					<div class="col-lg-4">
						<!-- Current Image -->
						@if($category->image)
						<div class="card mb-4">
							<div class="card-header">
								<h6 class="mb-0">Current Image</h6>
							</div>
							<div class="card-body text-center">
								<img src="{{ asset('storage/' . $category->image->path) }}" alt="Current Category Image" class="img-fluid rounded" style="max-height: 200px;">
								<p class="text-muted mt-2 mb-0"><small>Upload a new image to replace this one</small></p>
							</div>
						</div>
						@endif

						<!-- Statistics (Edit Mode Only) -->
						@if($category->id)
						<div class="card mb-4">
							<div class="card-header">
								<h6 class="mb-0">Category Statistics</h6>
							</div>
							<div class="card-body">
								<div class="row text-center">
									<div class="col-6 mb-3">
										<div class="card bg-info text-white">
											<div class="card-body py-2">
												<h5 class="mb-0">{{ $category->services()->count() }}</h5>
												<small>Services</small>
											</div>
										</div>
									</div>
									<div class="col-6 mb-3">
										<div class="card bg-success text-white">
											<div class="card-body py-2">
												<h5 class="mb-0">{{ $category->children()->count() }}</h5>
												<small>Sub-categories</small>
											</div>
										</div>
									</div>
								</div>
								<div class="text-center">
									@if($category->parent)
										<span class="badge badge-secondary">Sub-category</span>
									@else
										<span class="badge badge-primary">Main Category</span>
									@endif
									<br>
									@if($category->active)
										<span class="badge badge-success mt-1">Active</span>
									@else
										<span class="badge badge-danger mt-1">Inactive</span>
									@endif
								</div>
							</div>
						</div>

						<!-- Related Links -->
						<div class="card">
							<div class="card-header">
								<h6 class="mb-0">Quick Links</h6>
							</div>
							<div class="card-body">
								<div class="d-grid gap-2">
									<a href="{{ route('category.show', $category) }}" class="btn btn-outline-primary btn-sm">
										<i class="gd-eye"></i> View Details
									</a>
									@if($category->children()->count() > 0)
									<a href="{{ route('category.index', ['parent_filter' => $category->id]) }}" class="btn btn-outline-info btn-sm">
										<i class="gd-bookmark"></i> View Sub-categories
									</a>
									@endif
									@if($category->services()->count() > 0)
									<a href="{{ route('service.index', ['category_id' => $category->id]) }}" class="btn btn-outline-success btn-sm">
										<i class="gd-layers"></i> View Services
									</a>
									@endif
								</div>
							</div>
						</div>
						@else
						<!-- Help Card for New Categories -->
						<div class="card">
							<div class="card-header">
								<h6 class="mb-0">Help & Tips</h6>
							</div>
							<div class="card-body">
								<ul class="mb-0 small">
									<li>Choose a clear, descriptive name for your category</li>
									<li>Select the appropriate city where this category will be used</li>
									<li>Add a description to help users understand what this category includes</li>
									<li>If this is a sub-category, select the parent category</li>
									<li>Upload a representative image to help users identify the category</li>
									<li>Keep the category active unless you want to hide it from users</li>
								</ul>
							</div>
						</div>
						@endif
					</div>
				</div>

				<!-- Form Actions -->
				<div class="card mt-4">
					<div class="card-body">
						<div class="d-flex justify-content-between align-items-center">
							<div>
								<a href="{{ $category->id ? route('category.show', $category) : route('category.index') }}" class="btn btn-secondary">
									<i class="gd-arrow-left"></i> {{ $category->id ? 'Back to Details' : 'Back to List' }}
								</a>
							</div>
							<div>
								@if($category->id)
								<a href="{{ route('category.index') }}" class="btn btn-outline-secondary mr-2">Cancel</a>
								@endif
								<button type="submit" class="btn btn-primary">
									<i class="gd-{{ $category->id ? 'check' : 'plus' }}"></i> {{ $category->id ? 'Update Category' : 'Create Category' }}
								</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
		<!-- End Form -->
	</div>
</div>
@endsection