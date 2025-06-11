{{-- @if ($errors->any())
	<div class="alert alert-danger" role="alert">
		<div class="font-weight-bold">Oops! Please, fix the following errors:</div>
		<ul class="mb-0">
			@foreach ($errors->all() as $error)
				<li>{{ $error }}</li>
			@endforeach
		</ul>
	</div>
@endif --}}

{{-- @if (session('status'))
	<div class="alert alert-success" role="alert">
		{{ session('status') }}
	</div>
@endif



 --}}

{{-- Success Messages --}}
@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="gd-check-circle mr-2"></i>
        <strong>Success!</strong> {{ session('status') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

{{-- Error Messages --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="gd-alert mr-2"></i>
        <strong>Error!</strong> 
        <div style="white-space: pre-line;">{{ session('error') }}</div>
        @if(session('category_to_delete'))
            <div class="mt-3">
                <button type="button" class="btn btn-sm btn-danger" onclick="forceDelete({{ session('category_to_delete') }}, 'this category')">
                    <i class="gd-trash"></i> Force Delete All
                </button>
            </div>
        @endif
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

{{-- Warning Messages --}}
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="gd-alert-triangle mr-2"></i>
        <strong>Warning!</strong> 
        <div style="white-space: pre-line;">{{ session('warning') }}</div>
        @if(session('category_to_delete'))
            <div class="mt-3">
                <button type="button" class="btn btn-sm btn-warning" onclick="forceDelete({{ session('category_to_delete') }}, 'this category')">
                    <i class="gd-trash"></i> Proceed with Force Delete
                </button>
            </div>
        @endif
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

{{-- Info Messages --}}
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="gd-info-circle mr-2"></i>
        <strong>Info!</strong> {{ session('info') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

{{-- Validation Errors --}}
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="gd-alert mr-2"></i>
        <strong>Validation Errors!</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif