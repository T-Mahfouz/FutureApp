@extends('layouts.grain')

@section('title', 'Contact Message Details')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">
	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('contact.index') }}">Contact Messages</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">Message #{{ $contact->id }}</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
			<div class="d-flex align-items-center">
				<div>
					<h3 class="mb-0">Message from {{ $contact->name }}</h3>
					<small class="text-muted">
						Received {{ $contact->created_at ? $contact->created_at->format('M d, Y \a\t g:i A') : 'Unknown' }}
						@if($contact->is_read)
							<span class="badge badge-success ml-2">Read</span>
						@else
							<span class="badge badge-warning ml-2">Unread</span>
						@endif
					</small>
				</div>
			</div>
			<div>
				<form method="POST" action="{{ route('contact.toggle-read', $contact) }}" style="display: inline;">
					@csrf
					<button type="submit" class="btn btn-secondary">
						<i class="gd-{{ $contact->is_read ? 'envelope' : 'envelope-open' }}"></i> 
						{{ $contact->is_read ? 'Mark as Unread' : 'Mark as Read' }}
					</button>
				</form>
				<a href="{{ route('contact.index') }}" class="btn btn-primary">
					<i class="gd-arrow-left"></i> Back to Messages
				</a>
			</div>
		</div>

		<!-- Message Details -->
		<div class="row">
			<div class="col-md-8">
				<div class="card">
					<div class="card-header">
						<h5 class="mb-0">Message Content</h5>
					</div>
					<div class="card-body">
						<div class="message-content" style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff;">
							<p class="mb-0" style="line-height: 1.6; white-space: pre-wrap;">{{ $contact->message }}</p>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-4">
				<!-- Contact Information -->
				<div class="card mb-3">
					<div class="card-header">
						<h5 class="mb-0">Contact Information</h5>
					</div>
					<div class="card-body">
						<div class="mb-3">
							<strong>Name:</strong>
							<p class="mb-0">{{ $contact->name }}</p>
						</div>
						
						<div class="mb-3">
							<strong>Phone:</strong>
							<p class="mb-0">
								<a href="tel:{{ $contact->phone }}" class="text-decoration-none">
									<i class="gd-mobile"></i> {{ $contact->phone }}
								</a>
							</p>
						</div>
						
						<div class="mb-3">
							<strong>City:</strong>
							<p class="mb-0">
								<span class="badge badge-info">{{ $contact->city->name }}</span>
							</p>
						</div>
						
						@if($contact->user)
						<div class="mb-3">
							<strong>User Account:</strong>
							<p class="mb-0">
								<i class="gd-user text-success"></i> Registered User
								<br><small class="text-muted">{{ $contact->user->email }}</small>
							</p>
						</div>
						@else
						<div class="mb-3">
							<strong>User Account:</strong>
							<p class="mb-0">
								<i class="gd-user text-muted"></i> Guest User
							</p>
						</div>
						@endif
						
						<div class="mb-0">
							<strong>Received:</strong>
							<p class="mb-0">{{ $contact->created_at ? $contact->created_at->format('M d, Y \a\t g:i A') : 'Unknown' }}</p>
							<small class="text-muted">{{ $contact->created_at ? $contact->created_at->diffForHumans() : '' }}</small>
						</div>
					</div>
				</div>

				<!-- Quick Actions -->
				<div class="card">
					<div class="card-header">
						<h5 class="mb-0">Quick Actions</h5>
					</div>
					<div class="card-body">
						<div class="d-grid gap-2">
							<a href="tel:{{ $contact->phone }}" class="btn btn-success btn-sm">
								<i class="gd-mobile"></i> Call {{ $contact->name }}
							</a>
							
							<a href="sms:{{ $contact->phone }}" class="btn btn-info btn-sm">
								<i class="gd-comments"></i> Send SMS
							</a>
							
							@if($contact->phone)
							<a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->phone) }}" target="_blank" class="btn btn-success btn-sm">
								<i class="gd-comment"></i> WhatsApp
							</a>
							@endif
							
							<hr>
							
							<button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Delete this message? This action cannot be undone.')){document.getElementById('delete-form').submit();}">
								<i class="gd-trash"></i> Delete Message
							</button>
							
							<form id="delete-form" action="{{ route('contact.destroy', $contact) }}" method="POST" style="display: none;">
								<input type="hidden" name="_method" value="DELETE">
								@csrf
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Navigation -->
		@php
			$prevContact = \App\Models\ContactUs::where('id', '<', $contact->id)->orderBy('id', 'desc')->first();
			$nextContact = \App\Models\ContactUs::where('id', '>', $contact->id)->orderBy('id', 'asc')->first();
		@endphp
		
		@if($prevContact || $nextContact)
		<div class="row mt-4">
			<div class="col-12">
				<div class="d-flex justify-content-between">
					<div>
						@if($prevContact)
							<a href="{{ route('contact.show', $prevContact) }}" class="btn btn-outline-secondary">
								<i class="gd-arrow-left"></i> Previous Message
							</a>
						@endif
					</div>
					<div>
						@if($nextContact)
							<a href="{{ route('contact.show', $nextContact) }}" class="btn btn-outline-secondary">
								Next Message <i class="gd-arrow-right"></i>
							</a>
						@endif
					</div>
				</div>
			</div>
		</div>
		@endif

	</div>
</div>
@endsection