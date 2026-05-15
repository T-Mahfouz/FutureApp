<!-- Sidebar Nav -->
<aside id="sidebar" class="js-custom-scroll side-nav">
<ul id="sideNav" class="side-nav-menu side-nav-menu-top-level mb-0">
	<!-- Title -->
	<li class="sidebar-heading h6">Dashboard</li>
	<!-- End Title -->

	<!-- Dashboard -->
	<li class="side-nav-menu-item {{ Request::is('dashboard') ? 'active' : '' }}">
		<a class="side-nav-menu-link media align-items-center" href="{{ route('dashboard') }}">
		<span class="side-nav-menu-icon d-flex mr-3">
			<i class="gd-dashboard"></i>
		</span>
		<span class="side-nav-fadeout-on-closed media-body">Dashboard</span>
		</a>
	</li>
	<!-- End Dashboard -->
	
	@php
		$adminHasCityAssignments = Auth::guard('admin')->user()->cities()->count() > 0;
		$isSuperAdmin = !$adminHasCityAssignments;
	@endphp
	
	@if($isSuperAdmin)
	<!-- Analytics - Only for Super Admins -->
	<li class="side-nav-menu-item {{ Request::is('analytics*') ? 'active' : '' }}">
		<a class="side-nav-menu-link media align-items-center" href="{{ route('analytics') }}">
			<span class="side-nav-menu-icon d-flex mr-3">
				<i class="gd-bar-chart"></i>
			</span>
			<span class="side-nav-fadeout-on-closed media-body">Analytics</span>
		</a>
	</li>
	<!-- End Analytics -->

	<!-- Title - Administration (Only for Super Admins) -->
	<li class="sidebar-heading h6">Administration</li>
	<!-- End Title -->
	
	<!-- Admins -->
	<li class="side-nav-menu-item side-nav-has-menu {{ Request::is('admin*') ? 'active' : '' }}">
		<a class="side-nav-menu-link media align-items-center" href="#"
		data-target="#subAdmins">
		<span class="side-nav-menu-icon d-flex mr-3">
			<i class="gd-shield"></i>
		</span>
		<span class="side-nav-fadeout-on-closed media-body">Admins</span>
		<span class="side-nav-control-icon d-flex">
			<i class="gd-angle-right side-nav-fadeout-on-closed"></i>
		</span>
		<span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
		</a>

		<!-- Admins: sub -->
		<ul id="subAdmins" class="side-nav-menu side-nav-menu-second-level mb-0">
		<li class="side-nav-menu-item {{ Request::is('admin') && !Request::is('admin/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('admin.index') }}">All Admins</a>
		</li>
		<li class="side-nav-menu-item {{ Request::is('admin/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('admin.create') }}">Add Admin</a>
		</li>
		</ul>
		<!-- Admins: sub -->
	</li>
	<!-- End Admins -->
	
	<!-- Title - Users (Only for Super Admins) -->
	<li class="sidebar-heading h6">Users</li>
	<!-- End Title -->
	
	<!-- Users -->
	<li class="side-nav-menu-item side-nav-has-menu {{ Request::is('user*') ? 'active' : '' }}">
		<a class="side-nav-menu-link media align-items-center" href="#"
		data-target="#subUsers">
		<span class="side-nav-menu-icon d-flex mr-3">
			<i class="gd-user"></i>
		</span>
		<span class="side-nav-fadeout-on-closed media-body">Users</span>
		<span class="side-nav-control-icon d-flex">
			<i class="gd-angle-right side-nav-fadeout-on-closed"></i>
		</span>
		<span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
		</a>

		<!-- Users: sub -->
		<ul id="subUsers" class="side-nav-menu side-nav-menu-second-level mb-0">
		<li class="side-nav-menu-item {{ Request::is('user') && !Request::is('user/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('user.index') }}">All Users</a>
		</li>
		<li class="side-nav-menu-item {{ Request::is('user/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('user.create') }}">Add User</a>
		</li>
		</ul>
		<!-- Users: sub -->
	</li>
	<!-- End Users -->
	
	<!-- Title - System Management (Only for Super Admins) -->
	<li class="sidebar-heading h6">System Management</li>
	<!-- End Title -->
	
	<!-- Cities -->
	<li class="side-nav-menu-item side-nav-has-menu {{ Request::is('city*') || Request::is('cities*') ? 'active' : '' }}">
		<a class="side-nav-menu-link media align-items-center" href="#"
		data-target="#subCitiesSuper">
		<span class="side-nav-menu-icon d-flex mr-3">
			<i class="gd-map-alt"></i>
		</span>
		<span class="side-nav-fadeout-on-closed media-body">Cities</span>
		<span class="side-nav-control-icon d-flex">
			<i class="gd-angle-right side-nav-fadeout-on-closed"></i>
		</span>
		<span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
		</a>

		<!-- Cities: sub -->
		<ul id="subCitiesSuper" class="side-nav-menu side-nav-menu-second-level mb-0">
		<li class="side-nav-menu-item {{ (Request::is('city') || Request::is('cities')) && !Request::is('city/create') && !Request::is('cities/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('city.index') }}">All Cities</a>
		</li>
		<li class="side-nav-menu-item {{ Request::is('city/create') || Request::is('cities/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('city.create') }}">Add City</a>
		</li>
		</ul>
		<!-- Cities: sub -->
	</li>
	<!-- End Cities -->
	@endif

	<!-- Title - Content Management -->
	<li class="sidebar-heading h6">Content Management</li>
	<!-- End Title -->

	<!-- Services - Available for both Super Admins and City Admins -->
	<li class="side-nav-menu-item side-nav-has-menu {{ Request::is('service*') || Request::is('services*') ? 'active' : '' }}">
		<a class="side-nav-menu-link media align-items-center" href="#"
		data-target="#subServices">
		<span class="side-nav-menu-icon d-flex mr-3">
			<i class="gd-layers"></i>
		</span>
		<span class="side-nav-fadeout-on-closed media-body">Services</span>
		<span class="side-nav-control-icon d-flex">
			<i class="gd-angle-right side-nav-fadeout-on-closed"></i>
		</span>
		<span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
		</a>

		<!-- Services: sub -->
		<ul id="subServices" class="side-nav-menu side-nav-menu-second-level mb-0">
		<li class="side-nav-menu-item {{ (Request::is('service') || Request::is('services')) && !Request::is('service/create') && !Request::is('services/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('service.index') }}">All Services</a>
		</li>
		<li class="side-nav-menu-item {{ Request::is('service/create') || Request::is('services/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('service.create') }}">Add Service</a>
		</li>
		</ul>
		<!-- Services: sub -->
	</li>
	<!-- End Services -->

	<!-- Categories - Available for both Super Admins and City Admins -->
	<li class="side-nav-menu-item side-nav-has-menu {{ Request::is('category*') || Request::is('categories*') ? 'active' : '' }}">
		<a class="side-nav-menu-link media align-items-center" href="#"
		data-target="#subCategories">
		<span class="side-nav-menu-icon d-flex mr-3">
			<i class="gd-bookmark"></i>
		</span>
		<span class="side-nav-fadeout-on-closed media-body">Categories</span>
		<span class="side-nav-control-icon d-flex">
			<i class="gd-angle-right side-nav-fadeout-on-closed"></i>
		</span>
		<span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
		</a>

		<!-- Categories: sub -->
		<ul id="subCategories" class="side-nav-menu side-nav-menu-second-level mb-0">
		<li class="side-nav-menu-item {{ (Request::is('category') || Request::is('categories')) && !Request::is('category/create') && !Request::is('categories/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('category.index') }}">All Categories</a>
		</li>
		<li class="side-nav-menu-item {{ Request::is('category/create') || Request::is('categories/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('category.create') }}">Add Category</a>
		</li>
		</ul>
		<!-- Categories: sub -->
	</li>
	<!-- End Categories -->

	<!-- News - Available for both Super Admins and City Admins -->
	<li class="side-nav-menu-item side-nav-has-menu {{ Request::is('news*') ? 'active' : '' }}">
		<a class="side-nav-menu-link media align-items-center" href="#"
			data-target="#subNews">
			<span class="side-nav-menu-icon d-flex mr-3">
			<i class="gd-file"></i>
			</span>
			<span class="side-nav-fadeout-on-closed media-body">News</span>
			<span class="side-nav-control-icon d-flex">
			<i class="gd-angle-right side-nav-fadeout-on-closed"></i>
			</span>
			<span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
		</a>

		<!-- News: sub -->
		<ul id="subNews" class="side-nav-menu side-nav-menu-second-level mb-0">
			<li class="side-nav-menu-item {{ Request::is('news') && !Request::is('news/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('news.index') }}">All News</a>
			</li>
			<li class="side-nav-menu-item {{ Request::is('news/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('news.create') }}">Add News</a>
			</li>
		</ul>
		<!-- News: sub -->
	</li>
	<!-- End News -->

	<!-- Ads - Available for both Super Admins and City Admins -->
	<li class="side-nav-menu-item side-nav-has-menu {{ Request::is('ad*') || Request::is('ads*') ? 'active' : '' }}">
		<a class="side-nav-menu-link media align-items-center" href="#"
		data-target="#subAds">
		<span class="side-nav-menu-icon d-flex mr-3">
			<i class="gd-image"></i>
		</span>
		<span class="side-nav-fadeout-on-closed media-body">Ads</span>
		<span class="side-nav-control-icon d-flex">
			<i class="gd-angle-right side-nav-fadeout-on-closed"></i>
		</span>
		<span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
		</a>

		<!-- Ads: sub -->
		<ul id="subAds" class="side-nav-menu side-nav-menu-second-level mb-0">
		<li class="side-nav-menu-item {{ (Request::is('ad') || Request::is('ads')) && !Request::is('ad/create') && !Request::is('ads/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('ad.index') }}">All Ads</a>
		</li>
		<li class="side-nav-menu-item {{ Request::is('ad/create') || Request::is('ads/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('ad.create') }}">Add Ad</a>
		</li>
		</ul>
		<!-- Ads: sub -->
	</li>
	<!-- End Ads -->

	<!-- Title - Communication -->
	<li class="sidebar-heading h6">Communication</li>
	<!-- End Title -->

	<!-- Notifications - Available for both Super Admins and City Admins -->
	<li class="side-nav-menu-item side-nav-has-menu {{ Request::is('notification*') || Request::is('notifications*') ? 'active' : '' }}">
		<a class="side-nav-menu-link media align-items-center" href="#"
		data-target="#subNotifications">
		<span class="side-nav-menu-icon d-flex mr-3">
			<i class="gd-bell"></i>
		</span>
		<span class="side-nav-fadeout-on-closed media-body">Notifications</span>
		<span class="side-nav-control-icon d-flex">
			<i class="gd-angle-right side-nav-fadeout-on-closed"></i>
		</span>
		<span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
		</a>

		<!-- Notifications: sub -->
		<ul id="subNotifications" class="side-nav-menu side-nav-menu-second-level mb-0">
		<li class="side-nav-menu-item {{ (Request::is('notification') || Request::is('notifications')) && !Request::is('notification/create') && !Request::is('notifications/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('notification.index') }}">All Notifications</a>
		</li>
		<li class="side-nav-menu-item {{ Request::is('notification/create') || Request::is('notifications/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('notification.create') }}">Add Notification</a>
		</li>
		<li class="side-nav-menu-item {{ Request::is('notification/send-firebase') || Request::is('notifications/send-firebase') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('notification.send-firebase') }}">Send Firebase Only</a>
		</li>
		</ul>
		<!-- Notifications: sub -->
	</li>
	<!-- End Notifications -->

	<!-- Contact Messages - Available for both Super Admins and City Admins -->
	<li class="side-nav-menu-item {{ Request::is('contact*') ? 'active' : '' }}">
		<a class="side-nav-menu-link media align-items-center" href="{{ route('contact.index') }}">
		<span class="side-nav-menu-icon d-flex mr-3">
			<i class="gd-envelope"></i>
		</span>
		<span class="side-nav-fadeout-on-closed media-body">Contact Messages</span>
		</a>
	</li>
	<!-- End Contact Messages -->

	<!-- Title - Settings -->
	<li class="sidebar-heading h6">Settings</li>
	<!-- End Title -->

  	<!-- Settings - Available for both Super Admins and City Admins -->
	<li class="side-nav-menu-item side-nav-has-menu {{ Request::is('setting*') || Request::is('settings*') ? 'active' : '' }}">
		<a class="side-nav-menu-link media align-items-center" href="#"
			data-target="#subSettings">
			<span class="side-nav-menu-icon d-flex mr-3">
			<i class="gd-settings"></i>
			</span>
			<span class="side-nav-fadeout-on-closed media-body">Settings</span>
			<span class="side-nav-control-icon d-flex">
			<i class="gd-angle-right side-nav-fadeout-on-closed"></i>
			</span>
			<span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
		</a>

		<!-- Settings: sub -->
		<ul id="subSettings" class="side-nav-menu side-nav-menu-second-level mb-0">
			<li class="side-nav-menu-item {{ (Request::is('setting') || Request::is('settings')) && !Request::is('setting/create') && !Request::is('settings/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('setting.index') }}">All Settings</a>
			</li>
			<li class="side-nav-menu-item {{ Request::is('setting/create') || Request::is('settings/create') ? 'active' : '' }}">
			<a class="side-nav-menu-link" href="{{ route('setting.create') }}">Add Setting</a>
			</li>
		</ul>
		<!-- Settings: sub -->
	</li>
	<!-- End Settings -->

	{{-- @if($adminHasCityAssignments)
	<!-- City Admin Info Section -->
	<li class="sidebar-heading h6">My Cities</li>
	<!-- End Title -->
	
	@foreach(Auth::guard('admin')->user()->cities as $city)
	<li class="side-nav-menu-item">
		<a class="side-nav-menu-link media align-items-center" href="{{ route('dashboard.city', $city->id) }}">
		<span class="side-nav-menu-icon d-flex mr-3">
			<i class="gd-map-pin"></i>
		</span>
		<span class="side-nav-fadeout-on-closed media-body">{{ $city->name }}</span>
		</a>
	</li>
	@endforeach
	@endif --}}

</ul>
</aside>
<!-- End Sidebar Nav -->