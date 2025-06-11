@extends('layouts.grain')

@section('title', 'Dashboard')

@section('content')

@include('components.notification')

<div id="feedback-container"></div>

<div class="card mb-3 mb-md-4">

	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('news.index') }}">News</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ $news->id ? 'Edit' : 'Create New' }} News</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">{{ $news->id ? 'Edit' : 'Create New' }} News</div>
		</div>

		<!-- Form -->
		<div>
			<form method="post" action="{{ $news->id ? route('news.update', $news) : route('news.store') }}" enctype="multipart/form-data">
                @if($news->id)
				<input type="hidden" name="_method" value="patch">
                @endif
				@csrf
				
				<div class="form-row">
					<div class="form-group col-12 col-md-6">
						<label for="name">News Title</label>
						<input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name', $news->name) }}" id="name" name="name" placeholder="Enter news title" required>
						@if($errors->has('name'))
							<div class="invalid-feedback">{{ $errors->first('name') }}</div>
						@endif
					</div>
					<div class="form-group col-12 col-md-6">
						<label for="city_id">City</label>
						<select class="form-control{{ $errors->has('city_id') ? ' is-invalid' : '' }}" id="city_id" name="city_id" required>
							<option value="">Select City</option>
							@foreach($cities as $city)
								<option value="{{ $city->id }}" {{ old('city_id', $news->city_id) == $city->id ? 'selected' : '' }}>
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
					<div class="form-group col-12">
						<label for="description">News Description</label>
						<textarea class="form-control{{ $errors->has('description') ? ' is-invalid' : '' }}" id="description" name="description" rows="8" placeholder="Enter news description" required>{{ old('description', $news->description) }}</textarea>
						@if($errors->has('description'))
							<div class="invalid-feedback">{{ $errors->first('description') }}</div>
						@endif
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-12 col-md-6">
						<label for="image">Main Image</label>
						<input type="file" class="form-control{{ $errors->has('image') ? ' is-invalid' : '' }}" id="image" name="image" accept="image/*">
						@if($errors->has('image'))
							<div class="invalid-feedback">{{ $errors->first('image') }}</div>
						@endif
						<small class="form-text text-muted">Upload a main image for this news</small>
					</div>
					<div class="form-group col-12 col-md-6">
						<label for="additional_images">Additional Images</label>
						<input type="file" class="form-control{{ $errors->has('additional_images.*') ? ' is-invalid' : '' }}" id="additional_images" name="additional_images[]" accept="image/*" multiple>
						@if($errors->has('additional_images.*'))
							<div class="invalid-feedback">{{ $errors->first('additional_images.*') }}</div>
						@endif
						<small class="form-text text-muted">Upload additional images (optional, multiple files allowed)</small>
					</div>
				</div>

				@if($news->image)
				<div class="form-row">
					<div class="form-group col-12">
						<label>Current Main Image</label>
						<div class="mt-2">
							<img src="{{ asset('storage/' . $news->image->path) }}" alt="Current News Image" class="img-thumbnail" style="max-width: 200px;">
							<p class="text-muted mt-1">Current news image - upload a new one to replace it</p>
						</div>
					</div>
				</div>
				@endif

				@if($news->id && $news->images->count() > 0)
				<div class="form-row">
					<div class="form-group col-12">
						<label>Current Additional Images</label>
						<div class="mt-2">
							<div class="row">
								@foreach($news->images as $image)
                                {{-- @dd($image) --}}
								<div class="col-md-3 mb-3" id="image-container-{{$image->pivot->id}}">
									<div class="card">
										<img src="{{ asset('storage/' . $image->path) }}" alt="Additional Image" class="card-img-top" style="height: 150px; object-fit: cover;">
										<div class="card-body p-2">
                                            <div style="cursor: pointer;" class="btn btn-sm btn-danger" onclick="removeImage(event, {{$image->pivot->id}})">Remove</div>
										</div>
									</div>
								</div>
								@endforeach
							</div>
							<p class="text-muted mt-1">Additional images - upload new ones to add more</p>
						</div>
					</div>
				</div>
				@endif

				<div class="d-flex justify-content-between">
					<a href="{{ route('news.index') }}" class="btn btn-secondary">Cancel</a>
					<button type="submit" class="btn btn-primary">{{ $news->id ? 'Update' : 'Create' }} News</button>
				</div>
			</form>
		</div>
		<!-- End Form -->
	</div>
</div>


<script>


const alertContainer = document.getElementById('feedback-container');
    
// Remove existing alerts
alertContainer.innerHTML = '';

function removeImage(event, id) {
    
    // feedback-container
    event.preventDefault();
    
    let devID = `image-container-${id}`;

    if (!confirm('Remove this image?')) {
        return;
    }

    const url = `{{ route('news.image.destroy', ['id' => ':id']) }}`.replace(':id', id);
    const csrf = $('meta[name="csrf-token"]').attr('content');

    let alertClass = '';
    let iconClass = '';
    let title = '';
    let message = '';

    

    $.ajax({
        method: "DELETE",
        url: url,
        headers: {
            'X-CSRF-TOKEN': csrf
        }
    })
    .done(function( data ) {

        alertClass = 'alert-success';
        iconClass = 'gd-info-circle';
        title = 'Success!';

        message = data.message
        
        $("#"+devID).remove();
        
    }).fail(function( err ) {
        alertClass = 'alert-danger';
        iconClass = 'gd-alert';
        title = 'Error!';
        message = err;

    }).always(function() {
        

        let alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${iconClass} mr-2"></i>
                <strong>${title}</strong> ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        alertContainer.innerHTML = alertHtml;
        
        window.scrollTo(0, 0);

    });
}

</script>
@endsection


