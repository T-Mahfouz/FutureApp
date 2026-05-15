@extends('layouts.grain')

@section('title', 'Notification')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">

	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('notification.index') }}">Notifications</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ $notification->id ? 'Edit' : 'Create New' }} Notification</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">{{ $notification->id ? 'Edit' : 'Create New' }} Notification</div>
		</div>

		<!-- Form -->
		<div>
			<form method="post" action="{{ $notification->id ? route('notification.update', $notification) : route('notification.store') }}" enctype="multipart/form-data" id="notificationForm">
                @if($notification->id)
				<input type="hidden" name="_method" value="patch">
                @endif
				@csrf
				
				<div class="form-row">
					<div class="form-group col-12 col-md-6">
						<label for="title">Notification Title</label>
						<input type="text" class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}" value="{{ old('title', $notification->title) }}" id="title" name="title" placeholder="Enter notification title" required>
						@if($errors->has('title'))
							<div class="invalid-feedback">{{ $errors->first('title') }}</div>
						@endif
					</div>
					<div class="form-group col-12 col-md-6">
						<label for="image">Notification Image</label>
						<input type="file" class="form-control{{ $errors->has('image') ? ' is-invalid' : '' }}" id="image" name="image" accept="image/*">
						@if($errors->has('image'))
							<div class="invalid-feedback">{{ $errors->first('image') }}</div>
						@endif
						<small class="form-text text-muted">Upload an image for this notification (optional)</small>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-12">
						<label for="body">Notification Body</label>
						<textarea class="form-control{{ $errors->has('body') ? ' is-invalid' : '' }}" id="body" name="body" rows="4" placeholder="Enter notification content" required>{{ old('body', $notification->body) }}</textarea>
						@if($errors->has('body'))
							<div class="invalid-feedback">{{ $errors->first('body') }}</div>
						@endif
					</div>
				</div>

				@if($notification->image)
				<div class="form-row">
					<div class="form-group col-12">
						<label>Current Image</label>
						<div class="mt-2">
							<img src="{{ asset('storage/' . $notification->image->path) }}" alt="Current Notification Image" class="img-thumbnail" style="max-width: 200px;">
							<p class="text-muted mt-1">Current notification image - upload a new one to replace it</p>
						</div>
					</div>
				</div>
				@endif

				<!-- City Selection (Required) -->
				<div class="form-row">
					<div class="form-group col-12">
						<label for="city_ids">Select Cities <span class="text-danger">*</span></label>
						<select class="form-control{{ $errors->has('city_ids') ? ' is-invalid' : '' }}" id="city_ids" name="city_ids[]" multiple required>
							@foreach($cities as $city)
								<option value="{{ $city->id }}" 
									{{ in_array($city->id, old('city_ids', $notification->cities->pluck('id')->toArray())) ? 'selected' : '' }}>
									{{ $city->name }}
								</option>
							@endforeach
						</select>
						@if($errors->has('city_ids'))
							<div class="invalid-feedback">{{ $errors->first('city_ids') }}</div>
						@endif
						@if($errors->has('city_ids.*'))
							<div class="invalid-feedback">{{ $errors->first('city_ids.*') }}</div>
						@endif
						<small class="form-text text-muted">Hold Ctrl/Cmd to select multiple cities. At least one city is required.</small>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-12 col-md-6">
						<label for="content_type">Related Content Type <span class="text-danger">*</span></label>
						<select class="form-control{{ $errors->has('content_type') ? ' is-invalid' : '' }}" id="content_type" name="content_type" required>
							<option value="">Select Content Type</option>
							<option value="service" {{ old('content_type', $notification->service_id ? 'service' : '') == 'service' ? 'selected' : '' }}>Related to Service</option>
							<option value="news" {{ old('content_type', $notification->news_id ? 'news' : '') == 'news' ? 'selected' : '' }}>Related to News</option>
						</select>
						@if($errors->has('content_type'))
							<div class="invalid-feedback">{{ $errors->first('content_type') }}</div>
						@endif
						@if($errors->has('service_or_news'))
							<div class="invalid-feedback">{{ $errors->first('service_or_news') }}</div>
						@endif
					</div>
					<div class="form-group col-12 col-md-6">
						<div class="form-check mt-4">
							<input class="form-check-input" type="checkbox" id="send_firebase" name="send_firebase" value="1" {{ old('send_firebase', true) ? 'checked' : '' }}>
							<label class="form-check-label" for="send_firebase">
								Send Firebase Notification
							</label>
							<small class="form-text text-muted">Check to send real-time notification via Firebase</small>
						</div>
					</div>
				</div>

				<!-- Service Selection (Hidden by default) -->
				<div class="form-row" id="service_selection" style="display: none;">
					<div class="form-group col-12 col-md-4">
						<label for="category_filter">Filter by Category</label>
						<select class="form-control" id="category_filter">
							<option value="">All Categories</option>
							@foreach($categories as $category)
								<option value="{{ $category->id }}">{{ $category->name }}</option>
							@endforeach
						</select>
						<small class="form-text text-muted">Filter services by category (optional)</small>
					</div>
					<div class="form-group col-12 col-md-8">
						<label for="service_id">Select Service</label>
						<select class="form-control{{ $errors->has('service_id') ? ' is-invalid' : '' }}" id="service_id" name="service_id">
							<option value="">Select cities first</option>
							@foreach($services as $service)
								<option value="{{ $service->id }}" {{ old('service_id', $notification->service_id) == $service->id ? 'selected' : '' }}>
									{{ $service->name }} ({{ $service->city->name ?? 'No City' }})
								</option>
							@endforeach
						</select>
						@if($errors->has('service_id'))
							<div class="invalid-feedback">{{ $errors->first('service_id') }}</div>
						@endif
					</div>
				</div>

				<!-- News Selection (Hidden by default) -->
				<div class="form-row" id="news_selection" style="display: none;">
					<div class="form-group col-12">
						<label for="news_id">Select News</label>
						<select class="form-control{{ $errors->has('news_id') ? ' is-invalid' : '' }}" id="news_id" name="news_id">
							<option value="">Select cities first</option>
							@foreach($news as $newsItem)
								<option value="{{ $newsItem->id }}" {{ old('news_id', $notification->news_id) == $newsItem->id ? 'selected' : '' }}>{{ $newsItem->name }}</option>
							@endforeach
						</select>
						@if($errors->has('news_id'))
							<div class="invalid-feedback">{{ $errors->first('news_id') }}</div>
						@endif
					</div>
				</div>

				<div class="d-flex justify-content-between">
					<a href="{{ route('notification.index') }}" class="btn btn-secondary">Cancel</a>
					<button type="submit" class="btn btn-primary">{{ $notification->id ? 'Update' : 'Create' }} Notification</button>
				</div>
			</form>
		</div>
		<!-- End Form -->
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const contentTypeSelect = document.getElementById('content_type');
	const serviceSelection = document.getElementById('service_selection');
	const newsSelection = document.getElementById('news_selection');
	const serviceSelect = document.getElementById('service_id');
	const newsSelect = document.getElementById('news_id');

	function toggleSelections() {
		const contentType = contentTypeSelect.value;
		const categoryFilter = document.getElementById('category_filter');

		// Hide all selections first
		serviceSelection.style.display = 'none';
		newsSelection.style.display = 'none';

		// Clear selections
		serviceSelect.value = '';
		newsSelect.value = '';

		// Reset category filter when switching content types
		if (categoryFilter) {
			categoryFilter.value = '';
		}

		// Show relevant selection based on content type
		switch(contentType) {
			case 'service':
				serviceSelection.style.display = 'block';
				break;
			case 'news':
				newsSelection.style.display = 'block';
				break;
		}
	}

	contentTypeSelect.addEventListener('change', toggleSelections);
	
	// Initialize on page load
	toggleSelections();
});
</script>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const citySelect = $('#city_ids');
    const serviceSelect = $('#service_id');
    const newsSelect = $('#news_id');
    const categoryFilter = $('#category_filter');
    const currentServiceId = {{ old('service_id', $notification->service_id) ?? 'null' }};
    const currentNewsId = {{ old('news_id', $notification->news_id) ?? 'null' }};

    // Load services and news on page load if cities are already selected
    if (citySelect.val() && citySelect.val().length > 0) {
        loadServicesAndNews(citySelect.val());
    }

    // Handle city change
    citySelect.on('change', function() {
        const cityIds = $(this).val();

        console.log('Cities changed to:', cityIds);

        // Reset category filter when cities change
        categoryFilter.val('');

        if (!cityIds || cityIds.length === 0) {
            serviceSelect.html('<option value="">Select cities first</option>');
            newsSelect.html('<option value="">Select cities first</option>');
            serviceSelect.prop('disabled', true);
            newsSelect.prop('disabled', true);
            return;
        }

        loadServicesAndNews(cityIds);
    });

    // Handle category filter change
    categoryFilter.on('change', function() {
        const cityIds = citySelect.val();
        if (cityIds && cityIds.length > 0) {
            loadServices(cityIds);
        }
    });

    function loadServicesAndNews(cityIds) {
        // Load services
        loadServices(cityIds);

        // Load news
        loadNews(cityIds);
    }

    function loadServices(cityIds) {
        // Show loading state
        serviceSelect.prop('disabled', true);
        serviceSelect.html('<option value="">Loading services...</option>');

        const url = '{{ route("notification.getServicesByCities") }}';
        const categoryId = categoryFilter.val();

        console.log('Fetching services from:', url, 'category:', categoryId);

        const requestData = { city_ids: cityIds };
        if (categoryId) {
            requestData.category_id = categoryId;
        }

        $.ajax({
            url: url,
            method: 'GET',
            data: requestData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(data) {
                console.log('Services response:', data);
                
                serviceSelect.html('<option value="">Select Service</option>');
                
                if (data.services && data.services.length > 0) {
                    $.each(data.services, function(index, service) {
                        const option = $('<option></option>')
                            .val(service.id)
                            .text(service.name + ' (' + service.city_name + ')');
                        
                        if (service.id == currentServiceId) {
                            option.prop('selected', true);
                        }
                        
                        serviceSelect.append(option);
                    });
                } else {
                    serviceSelect.html('<option value="">No services available for selected cities</option>');
                }
                
                serviceSelect.prop('disabled', false);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching services:', error);
                console.error('Response:', xhr.responseText);
                serviceSelect.html('<option value="">Error loading services</option>');
                serviceSelect.prop('disabled', false);
            }
        });
    }

    function loadNews(cityIds) {
        // Show loading state
        newsSelect.prop('disabled', true);
        newsSelect.html('<option value="">Loading news...</option>');

        const url = '{{ route("notification.getNewsByCities") }}';
        
        console.log('Fetching news from:', url);

        $.ajax({
            url: url,
            method: 'GET',
            data: {
                city_ids: cityIds
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(data) {
                console.log('News response:', data);
                
                newsSelect.html('<option value="">Select News</option>');
                
                if (data.news && data.news.length > 0) {
                    $.each(data.news, function(index, newsItem) {
                        const option = $('<option></option>')
                            .val(newsItem.id)
                            .text(newsItem.name + ' (' + newsItem.city_name + ')');
                        
                        if (newsItem.id == currentNewsId) {
                            option.prop('selected', true);
                        }
                        
                        newsSelect.append(option);
                    });
                } else {
                    newsSelect.html('<option value="">No news available for selected cities</option>');
                }
                
                newsSelect.prop('disabled', false);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching news:', error);
                console.error('Response:', xhr.responseText);
                newsSelect.html('<option value="">Error loading news</option>');
                newsSelect.prop('disabled', false);
            }
        });
    }
});
</script>
@endsection