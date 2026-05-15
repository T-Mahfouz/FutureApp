@extends('layouts.grain')

@section('title', 'Settings')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">

	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('setting.index') }}">Settings</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ $setting->id ? 'Edit' : 'Create New' }} Setting</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between">
			<div class="h3 mb-0">{{ $setting->id ? 'Edit' : 'Create New' }} Setting</div>
		</div>

		<!-- Form -->
		<div>
			<form method="post" action="{{ $setting->id ? route('setting.update', $setting) : route('setting.store') }}">
                @if($setting->id)
				<input type="hidden" name="_method" value="patch">
                @endif
				@csrf
				
				<div class="form-row">
					<div class="form-group col-12 col-md-6">
						<label for="city_id">City</label>
						<select class="form-control{{ $errors->has('city_id') ? ' is-invalid' : '' }}" id="city_id" name="city_id" required>
							<option value="">Select City</option>
							@foreach($cities as $city)
								<option value="{{ $city->id }}" {{ old('city_id', $setting->city_id) == $city->id ? 'selected' : '' }}>
									{{ $city->name }}
								</option>
							@endforeach
						</select>
						@if($errors->has('city_id'))
							<div class="invalid-feedback">{{ $errors->first('city_id') }}</div>
						@endif
					</div>
					<div class="form-group col-12 col-md-6">
						<label for="key">Setting Key</label>
                        <select name="key" id="key" class="form-control{{ $errors->has('key') ? ' is-invalid' : '' }}">
                            <option value="contact_us">Contact Us</option>
                            <option value="about_us">About Us</option>
                            <option value="privacy_policy">Privacy Policy</option>
                            <option value="terms_conditions">Terms & Conditions</option>
                            <option value="help">Help</option>
                            <option value="faq">FAQ</option>
                        </select>
						<small class="form-text text-muted">Use snake_case format (e.g., site_title, footer_text)</small>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-12">
						<label for="value">Setting Value</label>
						<textarea id="value" name="value" class="form-control{{ $errors->has('value') ? ' is-invalid' : '' }}" rows="10">{{ old('value', $setting->value) }}</textarea>
						@if($errors->has('value'))
							<div class="invalid-feedback">{{ $errors->first('value') }}</div>
						@endif
						<small class="form-text text-muted">This field supports HTML content and rich text formatting</small>
					</div>
				</div>

				<div class="d-flex justify-content-between">
					<a href="{{ route('setting.index') }}" class="btn btn-secondary">Cancel</a>
					<button type="submit" class="btn btn-primary">{{ $setting->id ? 'Update' : 'Create' }} Setting</button>
				</div>
			</form>
		</div>
		<!-- End Form -->
	</div>
</div>

@endsection

@section('scripts')
<!-- Include CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
ClassicEditor
    .create(document.querySelector('#value'), {
        toolbar: {
            items: [
                'heading',
                '|',
                'bold',
                'italic',
                'link',
                'bulletedList',
                'numberedList',
                '|',
                'outdent',
                'indent',
                '|',
                'blockQuote',
                'insertTable',
                'undo',
                'redo'
            ]
        },
        language: 'en',
        table: {
            contentToolbar: [
                'tableColumn',
                'tableRow',
                'mergeTableCells'
            ]
        }
    })
    .then(editor => {
        window.editor = editor;
        
        // Set editor height to 500px
        editor.editing.view.change(writer => {
            writer.setStyle('height', '250px', editor.editing.view.document.getRoot());
        });
        
        // Hide the original textarea after CKEditor is initialized
        document.querySelector('#value').style.display = 'none';
        
        // Handle form submission to sync CKEditor content
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const textarea = document.querySelector('#value');
            const content = editor.getData();
            
            // Update textarea value
            textarea.value = content;
            
            // Remove required attribute temporarily to avoid validation issues
            textarea.removeAttribute('required');
            
            // Check if content is empty and prevent submission if required
            if (!content.trim()) {
                e.preventDefault();
                alert('Please enter some content in the editor.');
                return false;
            }
        });
    })
    .catch(error => {
        console.error('Error initializing CKEditor:', error);
        // Show the textarea if CKEditor fails to load
        document.querySelector('#value').style.display = 'block';
    });
</script>
@endsection