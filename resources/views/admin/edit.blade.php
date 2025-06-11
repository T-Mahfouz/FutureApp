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
					<a href="{{ route('admin.index') }}">Admins</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ $admin->id ? 'Edit' : 'Create New' }} Admin</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">{{ $admin->id ? 'Edit' : 'Create New' }} Admin</div>
		</div>

		<!-- Form -->
		<div>
			<form method="post" action="{{ $admin->id ? route('admin.update', $admin) : route('admin.store') }}" enctype="multipart/form-data">
                @if($admin->id)
				<input type="hidden" name="_method" value="patch">
                @endif
				@csrf
				
				<div class="form-row">
					<div class="form-group col-12 col-md-6">
						<label for="name">Name</label>
						<input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name', $admin->name) }}" id="name" name="name" placeholder="Admin Name" required>
						@if($errors->has('name'))
							<div class="invalid-feedback">{{ $errors->first('name') }}</div>
						@endif
					</div>
					<div class="form-group col-12 col-md-6">
						<label for="email">Email</label>
						<input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email', $admin->email) }}" id="email" name="email" placeholder="Admin Email" required>
						@if($errors->has('email'))
							<div class="invalid-feedback">{{ $errors->first('email') }}</div>
						@endif
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-12 col-md-6">
						<label for="password">Password {{ $admin->id ? '(Leave empty to keep current)' : '' }}</label>
						<input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" id="password" name="password" placeholder="Password" {{ $admin->id ? '' : 'required' }}>
						@if($errors->has('password'))
							<div class="invalid-feedback">{{ $errors->first('password') }}</div>
						@endif
					</div>
					<div class="form-group col-12 col-md-6">
						<label for="password_confirmation">Confirm Password</label>
						<input type="password" class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password">
						@if($errors->has('password_confirmation'))
							<div class="invalid-feedback">{{ $errors->first('password_confirmation') }}</div>
						@endif
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-12 col-md-6">
						<label for="image">Profile Image</label>
						<input type="file" class="form-control{{ $errors->has('image') ? ' is-invalid' : '' }}" id="image" name="image" accept="image/*">
						@if($errors->has('image'))
							<div class="invalid-feedback">{{ $errors->first('image') }}</div>
						@endif
						@if($admin->image)
							<div class="mt-2">
								<img src="{{ asset('storage/' . $admin->image->path) }}" alt="Current Image" class="img-thumbnail" width="100">
								<small class="text-muted d-block">Current image</small>
							</div>
						@endif
					</div>
					<div class="form-group col-12 col-md-6">
						<label for="cities">Assigned Cities</label>
						<select multiple class="form-control{{ $errors->has('cities') ? ' is-invalid' : '' }}" id="cities" name="cities[]" size="6">
							@foreach($cities as $city)
								<option value="{{ $city->id }}" 
									{{ in_array($city->id, old('cities', $admin->cities->pluck('id')->toArray())) ? 'selected' : '' }}>
									{{ $city->name }}
								</option>
							@endforeach
						</select>
						<small class="form-text text-muted">Hold Ctrl/Cmd to select multiple cities</small>
						@if($errors->has('cities'))
							<div class="invalid-feedback">{{ $errors->first('cities') }}</div>
						@endif
					</div>
				</div>

				<button type="submit" class="btn btn-primary float-right">{{ $admin->id ? 'Update' : 'Create' }}</button>
			</form>
		</div>
		<!-- End Form -->
	</div>
</div>
@endsection