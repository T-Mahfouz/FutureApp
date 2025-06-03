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
					<a href="{{ route('city.index') }}">Cities</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ $city->id ? 'Edit' : 'Create New' }} City</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">{{ $city->id ? 'Edit' : 'Create New' }} City</div>
		</div>

		<!-- Form -->
		<div>
			<form method="post" action="{{ $city->id ? route('city.update', $city) : route('city.store') }}" enctype="multipart/form-data">
                @if($city->id)
				<input type="hidden" name="_method" value="patch">
                @endif
				@csrf
				
				<div class="form-row">
					<div class="form-group col-12 col-md-6">
						<label for="name">City Name</label>
						<input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name', $city->name) }}" id="name" name="name" placeholder="Enter city name" required>
						@if($errors->has('name'))
							<div class="invalid-feedback">{{ $errors->first('name') }}</div>
						@endif
					</div>
					<div class="form-group col-12 col-md-6">
						<label for="image">City Image</label>
						<input type="file" class="form-control{{ $errors->has('image') ? ' is-invalid' : '' }}" id="image" name="image" accept="image/*">
						@if($errors->has('image'))
							<div class="invalid-feedback">{{ $errors->first('image') }}</div>
						@endif
						<small class="form-text text-muted">Upload an image to represent this city</small>
					</div>
				</div>

				@if($city->image)
				<div class="form-row">
					<div class="form-group col-12">
						<label>Current Image</label>
						<div class="mt-2">
							<img src="{{ asset('storage/' . $city->image->path) }}" alt="Current City Image" class="img-thumbnail" style="max-width: 200px;">
							<p class="text-muted mt-1">Current city image - upload a new one to replace it</p>
						</div>
					</div>
				</div>
				@endif

				@if($city->id)
				<div class="form-row">
					<div class="form-group col-12">
						<label>City Statistics</label>
						<div class="row">
							<div class="col-md-3">
								<div class="card bg-info text-white">
									<div class="card-body text-center">
										<h4>{{ $city->services()->count() }}</h4>
										<small>Services</small>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="card bg-success text-white">
									<div class="card-body text-center">
										<h4>{{ $city->categories()->count() }}</h4>
										<small>Categories</small>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="card bg-warning text-white">
									<div class="card-body text-center">
										<h4>{{ $city->admins()->count() }}</h4>
										<small>Admins</small>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="card bg-secondary text-white">
									<div class="card-body text-center">
										<h4>{{ $city->users()->count() }}</h4>
										<small>Users</small>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				@endif

				<div class="d-flex justify-content-between">
					<a href="{{ route('city.index') }}" class="btn btn-secondary">Cancel</a>
					<button type="submit" class="btn btn-primary">{{ $city->id ? 'Update' : 'Create' }} City</button>
				</div>
			</form>
		</div>
		<!-- End Form -->
	</div>
</div>
@endsection