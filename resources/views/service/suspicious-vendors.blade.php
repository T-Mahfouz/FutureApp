@extends('layouts.grain')

@section('title', 'Suspicious Vendors')

@section('content')

@include('components.notification')

<div class="card mb-3 mb-md-4">
	<div class="card-body">
		<!-- Breadcrumb -->
		<nav class="d-none d-md-block" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('service.index') }}">Services</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">Suspicious Vendors</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4">
			<div class="h3 mb-1">Top Suspicious Vendors</div>
			<small class="text-muted">
				Services that consistently appear as the beneficiary of suspicious rating activity — i.e.
				they received high ratings from users who, at the same time, gave low ratings to competing
				services in the same city + category. A vendor with many distinct raters all pushing the
				same pattern is the strongest indicator of coordinated effort.
			</small>
		</div>

		<!-- Filters -->
		<form method="GET" action="{{ route('service.suspicious-vendors') }}" class="mb-0">
			<div class="row">
				<div class="col-md-3 mb-2">
					<label class="form-label small">User phone</label>
					<input type="text" class="form-control" name="phone" value="{{ $filters['phoneSearch'] }}" placeholder="Search by phone...">
				</div>
				<div class="col-md-3 mb-2">
					<label class="form-label small">City</label>
					<select class="form-control" name="city_id">
						<option value="">All accessible cities</option>
						@foreach($cities as $city)
							<option value="{{ $city->id }}" {{ $filters['cityFilter'] == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-3 mb-2">
					<label class="form-label small">Date from</label>
					<input type="date" class="form-control" name="date_from" value="{{ $filters['dateFrom'] }}">
				</div>
				<div class="col-md-3 mb-2">
					<label class="form-label small">Date to</label>
					<input type="date" class="form-control" name="date_to" value="{{ $filters['dateTo'] }}">
				</div>
			</div>
			<div class="row">
				<div class="col-md-3 mb-2">
					<label class="form-label small">High threshold (≥)</label>
					<select class="form-control" name="high">
						@foreach([3,4,5] as $v)
							<option value="{{ $v }}" {{ $filters['highThreshold'] == $v ? 'selected' : '' }}>{{ $v }} ★</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-3 mb-2">
					<label class="form-label small">Low threshold (≤)</label>
					<select class="form-control" name="low">
						@foreach([1,2,3] as $v)
							<option value="{{ $v }}" {{ $filters['lowThreshold'] == $v ? 'selected' : '' }}>{{ $v }} ★</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-3 mb-2">
					<label class="form-label small">Window (days)</label>
					<input type="number" class="form-control" name="window" min="1" max="365" value="{{ $filters['windowDays'] }}">
				</div>
				<div class="col-md-3 mb-2 d-flex align-items-end">
					<button type="submit" class="btn btn-primary mr-2">
						<i class="gd-search"></i> Apply
					</button>
					<a href="{{ route('service.suspicious-vendors') }}" class="btn btn-outline-secondary">Reset</a>
				</div>
			</div>
		</form>
	</div>
</div>

<div class="mb-3">
	<strong>{{ $vendors->total() }}</strong> suspicious vendor{{ $vendors->total() === 1 ? '' : 's' }} found.
</div>

@if($vendors->total() === 0)
	<div class="card">
		<div class="card-body">
			<div class="alert alert-success mb-0">
				<i class="gd-check"></i> No suspicious vendors detected for the current filters.
			</div>
		</div>
	</div>
@else
<div class="card mb-4">
	<div class="card-body">
		<div class="alert alert-info">
			<strong><i class="gd-info-circle"></i> How to read this:</strong>
			Each row is a service flagged as a likely beneficiary of coordinated rating manipulation.
			Higher "Pairs" and "Distinct raters" values increase confidence. Treat as investigative leads,
			not proof.
		</div>

		<div class="table-responsive">
			<table class="table align-middle mb-0">
				<thead>
					<tr>
						<th style="width: 50px;">#</th>
						<th>Service</th>
						<th>City</th>
						<th class="text-center">Pairs</th>
						<th class="text-center">Distinct raters</th>
						<th class="text-center">Competitors hit</th>
						<th>Raters</th>
						<th>Competitors</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@foreach($vendors as $rank => $v)
					<tr>
						<td><strong>#{{ ($vendors->firstItem() ?? 1) + $rank }}</strong></td>
						<td>
							@if($v['service'])
								<a href="{{ route('service.show', $v['service']) }}"><strong>{{ $v['service']->name }}</strong></a>
							@else
								<span class="text-muted font-italic">Deleted service</span>
							@endif
						</td>
						<td>{{ $v['service']->city->name ?? '—' }}</td>
						<td class="text-center"><span class="badge badge-danger" style="font-size: 13px;">{{ $v['pair_count'] }}</span></td>
						<td class="text-center"><span class="badge badge-warning" style="font-size: 13px;">{{ count($v['raters']) }}</span></td>
						<td class="text-center"><span class="badge badge-secondary" style="font-size: 13px;">{{ count($v['competitors']) }}</span></td>
						<td>
							@php $raters = array_values($v['raters']); @endphp
							@foreach(array_slice($raters, 0, 3) as $rater)
								<a href="{{ route('user.show', $rater) }}" class="badge badge-light mr-1 mb-1">{{ $rater->name }}</a>
							@endforeach
							@if(count($raters) > 3)
								<small class="text-muted">+{{ count($raters) - 3 }} more</small>
							@endif
						</td>
						<td>
							@php $competitors = array_values($v['competitors']); @endphp
							@foreach(array_slice($competitors, 0, 3) as $comp)
								<a href="{{ route('service.show', $comp) }}" class="badge badge-light mr-1 mb-1">{{ $comp->name }}</a>
							@endforeach
							@if(count($competitors) > 3)
								<small class="text-muted">+{{ count($competitors) - 3 }} more</small>
							@endif
						</td>
						<td>
							@if($v['service'])
								<a href="{{ route('service.show', $v['service']) }}" class="btn btn-sm btn-outline-primary" title="View service">
									<i class="gd-eye"></i>
								</a>
							@endif
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>

		{{ $vendors->links('components.pagination') }}
	</div>
</div>
@endif

@endsection
