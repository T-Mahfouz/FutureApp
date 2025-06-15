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
					<a href="{{ route('user.index') }}">Users</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ $user->id ? 'Edit' : 'Create New' }} User</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">{{ $user->id ? 'Edit' : 'Create New' }} User</div>
		</div>

		<!-- Form -->
		<div>
			<form method="post" action="{{ $user->id ? route('user.update', $user) : route('user.store') }}" enctype="multipart/form-data"> <!-- NEW: Add enctype for file upload -->
                @if($user->id)
				<input type="hidden" name="_method" value="patch">
                @endif
				@csrf
				
				<div class="row">
					<!-- Main Form -->
					<div class="col-lg-8">
						<div class="card">
							<div class="card-header">
								<h6 class="mb-0">User Information</h6>
							</div>
							<div class="card-body">
								<div class="form-row">
									<div class="form-group col-12 col-md-6">
										<label for="name">Name <span class="text-danger">*</span></label>
										<input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name', $user->name) }}" id="name" name="name" placeholder="User Name" required>
										@if($errors->has('name'))
											<div class="invalid-feedback">{{ $errors->first('name') }}</div>
										@endif
									</div>
									<div class="form-group col-12 col-md-6">
										<label for="email">Email <span class="text-danger">*</span></label>
										<input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email', $user->email) }}" id="email" name="email" placeholder="User Email" required>
										@if($errors->has('email'))
											<div class="invalid-feedback">{{ $errors->first('email') }}</div>
										@endif
									</div>
								</div>

								<div class="form-row">
									<div class="form-group col-12 col-md-6">
										<label for="phone">Phone</label>
										<input type="text" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" value="{{ old('phone', $user->phone) }}" id="phone" name="phone" placeholder="Phone Number">
										@if($errors->has('phone'))
											<div class="invalid-feedback">{{ $errors->first('phone') }}</div>
										@endif
									</div>
									<div class="form-group col-12 col-md-6">
										<label for="city_id">City</label>
										<select class="form-control{{ $errors->has('city_id') ? ' is-invalid' : '' }}" id="city_id" name="city_id">
											<option value="">Select City</option>
											@foreach($cities as $city)
												<option value="{{ $city->id }}" {{ old('city_id', $user->city_id) == $city->id ? 'selected' : '' }}>
													{{ $city->name }}
												</option>
											@endforeach
										</select>
										@if($errors->has('city_id'))
											<div class="invalid-feedback">{{ $errors->first('city_id') }}</div>
										@endif
									</div>
								</div>

								<!-- NEW: Profile Image field -->
								<div class="form-row">
									<div class="form-group col-12">
										<label for="image">Profile Image</label>
										<input type="file" class="form-control{{ $errors->has('image') ? ' is-invalid' : '' }}" id="image" name="image" accept="image/*">
										@if($errors->has('image'))
											<div class="invalid-feedback">{{ $errors->first('image') }}</div>
										@endif
										<small class="form-text text-muted">Upload a profile image for this user (JPEG, PNG, JPG, GIF - Max: 2MB)</small>
									</div>
								</div>

								<div class="form-row">
									<div class="form-group col-12 col-md-6">
										<label for="password">Password {{ $user->id ? '(Leave empty to keep current)' : '<span class="text-danger">*</span>' }}</label>
										<input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" id="password" name="password" placeholder="Password" {{ !$user->id ? 'required' : '' }}>
										@if($errors->has('password'))
											<div class="invalid-feedback">{{ $errors->first('password') }}</div>
										@endif
									</div>
									<div class="form-group col-12 col-md-6">
										<label for="password_confirmation">Confirm Password {{ !$user->id ? '<span class="text-danger">*</span>' : '' }}</label>
										<input type="password" class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password" {{ !$user->id ? 'required' : '' }}>
										@if($errors->has('password_confirmation'))
											<div class="invalid-feedback">{{ $errors->first('password_confirmation') }}</div>
										@endif
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Sidebar -->
					<div class="col-lg-4">
						<!-- Current Image -->
						@if($user->image)
						<div class="card mb-4">
							<div class="card-header">
								<h6 class="mb-0">Current Profile Image</h6>
							</div>
							<div class="card-body text-center">
								<img src="{{ asset('storage/' . $user->image->path) }}" alt="Current Profile Image" class="img-fluid rounded-circle" style="max-height: 200px; max-width: 200px;">
								<p class="text-muted mt-2 mb-0"><small>Upload a new image to replace this one</small></p>
							</div>
						</div>
						@endif

						<!-- User Stats (Edit Mode Only) -->
						@if($user->id)
						<div class="card mb-4">
							<div class="card-header">
								<h6 class="mb-0">User Information</h6>
							</div>
							<div class="card-body">
								<div class="text-center">
									<div class="mb-3">
										@if($user->image)
											<img src="{{ asset('storage/' . $user->image->path) }}" alt="{{ $user->name }}" class="rounded-circle" width="80" height="80">
										@else
											<span class="avatar-placeholder bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 24px;">
												{{ substr($user->name, 0, 1) }}
											</span>
										@endif
									</div>
									<h6>{{ $user->name }}</h6>
									<small class="text-muted">{{ $user->email }}</small>
									<br>
									@if($user->city)
										<span class="badge badge-primary mt-1">{{ $user->city->name }}</span>
									@endif
								</div>
								
								<hr>
								
								<div class="row text-center">
									<div class="col-6">
										<strong>{{ $user->favorites()->count() }}</strong>
										<br><small class="text-muted">Favorites</small>
									</div>
									<div class="col-6">
										<strong>{{ $user->rates()->count() }}</strong>
										<br><small class="text-muted">Reviews</small>
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
									<a href="{{ route('user.index') }}" class="btn btn-outline-secondary btn-sm">
										<i class="gd-arrow-left"></i> Back to Users
									</a>
								</div>
							</div>
						</div>
						@else
						<!-- Help Card for New Users -->
						<div class="card">
							<div class="card-header">
								<h6 class="mb-0">Help & Tips</h6>
							</div>
							<div class="card-body">
								<ul class="mb-0 small">
									<li>Enter the user's full name</li>
									<li>Provide a valid email address (must be unique)</li>
									<li><strong>NEW:</strong> Upload a profile image for better user identification</li> <!-- NEW -->
									<li>Phone number is optional but recommended</li>
									<li>Select the user's city if applicable</li>
									<li>Set a secure password (minimum 6 characters)</li>
									<li>Confirm the password to avoid typos</li>
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
								<a href="{{ route('user.index') }}" class="btn btn-secondary">
									<i class="gd-arrow-left"></i> Back to Users
								</a>
							</div>
							<div>
								<button type="submit" class="btn btn-primary">
									<i class="gd-{{ $user->id ? 'check' : 'plus' }}"></i> {{ $user->id ? 'Update User' : 'Create User' }}
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