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
			<form method="post" action="{{ $user->id ? route('user.update', $user) : route('user.store') }}">
                @if($user->id)
				<input type="hidden" name="_method" value="patch">
                @endif
				@csrf
				<div class="form-row">
					<div class="form-group col-12 col-md-6">
						<label for="name">Name</label>
						<input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name', $user->name) }}" id="name" name="name" placeholder="User Name">
						@if($errors->has('name'))
							<div class="invalid-feedback">{{ $errors->first('name') }}</div>
						@endif
					</div>
					<div class="form-group col-12 col-md-6">
						<label for="email">Email</label>
						<input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email', $user->email) }}" id="email" name="email" placeholder="User Email">
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

				<div class="form-row">
					<div class="form-group col-12 col-md-6">
						<label for="password">Password {{ $user->id ? '(Leave empty to keep current)' : '' }}</label>
						<input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" id="password" name="password" placeholder="Password">
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

				<button type="submit" class="btn btn-primary float-right">{{ $user->id ? 'Update' : 'Create' }}</button>
			</form>
		</div>
		<!-- End Form -->
	</div>
</div>
@endsection