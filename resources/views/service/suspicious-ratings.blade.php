@extends('layouts.grain')

@section('title', 'Suspicious Ratings')

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
				<li class="breadcrumb-item active" aria-current="page">Suspicious Ratings</li>
			</ol>
		</nav>
		<!-- End Breadcrumb -->

		<div class="mb-3 mb-md-4">
			<div class="h3 mb-1">Suspicious Rating Behavior</div>
			<small class="text-muted">
				Users whose rating pattern matches the abuse signature: gave a high rating to one service
				AND a low rating to a competing service (same city + at least one shared category) within
				the configured time window. Results are grouped per user.
			</small>
		</div>

		<!-- Filters -->
		<form method="GET" action="{{ route('service.suspicious-ratings') }}" class="mb-0">
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
					<a href="{{ route('service.suspicious-ratings') }}" class="btn btn-outline-secondary">Reset</a>
				</div>
			</div>
		</form>
	</div>
</div>

<div class="mb-3">
	<strong>{{ $groups->total() }}</strong> suspicious user{{ $groups->total() === 1 ? '' : 's' }} found
	@if($groups->total() > 0)
		({{ collect($groups->items())->sum('pair_count') }} pair{{ collect($groups->items())->sum('pair_count') === 1 ? '' : 's' }} on this page).
	@else
		.
	@endif
</div>

@if($groups->total() === 0)
	<div class="card">
		<div class="card-body">
			<div class="alert alert-success mb-0">
				<i class="gd-check"></i> No suspicious rating behavior detected for the current filters.
			</div>
		</div>
	</div>
@else
	@foreach($groups as $group)
	@php $user = $group['user']; @endphp
	<div class="card mb-4">
		<div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="cursor: pointer;"
			data-toggle="collapse" data-target="#user-card-{{ $user->id }}" aria-expanded="false" aria-controls="user-card-{{ $user->id }}">
			<div class="d-flex align-items-center">
				@if($user->image && $user->image->path)
					<img src="{{ asset('storage/' . $user->image->path) }}" alt="{{ $user->name }}" class="rounded-circle mr-3" width="56" height="56" style="object-fit:cover;">
				@else
					<span class="avatar-placeholder bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mr-3" style="width:56px;height:56px;font-size:20px;">
						{{ substr($user->name, 0, 1) }}
					</span>
				@endif
				<div>
					<h5 class="mb-0">
						<a href="{{ route('user.show', $user) }}" onclick="event.stopPropagation();">{{ $user->name }}</a>
						@if($user->blocked)
							<span class="badge badge-danger ml-2">Blocked</span>
						@endif
					</h5>
					<small class="text-muted">
						{{ $user->phone ?? $user->email }}
						@if($user->city) · {{ $user->city->name }} @endif
					</small>
				</div>
			</div>
			<div onclick="event.stopPropagation();">
				<span class="badge badge-danger" style="font-size: 13px;">{{ $group['pair_count'] }} suspicious pair{{ $group['pair_count'] === 1 ? '' : 's' }}</span>
				<a href="{{ route('user.show', $user) }}" class="btn btn-sm btn-outline-secondary ml-2">
					<i class="gd-user"></i> View profile
				</a>
				<i class="gd-angle-down ml-2 collapse-indicator"></i>
			</div>
		</div>
		<div id="user-card-{{ $user->id }}" class="collapse">
		<div class="card-body">
			<!-- Stats + chart -->
			<div class="row mb-4">
				<div class="col-md-7">
					<div class="row">
						<div class="col-6 col-md-3 mb-3">
							<div class="card bg-info text-white h-100">
								<div class="card-body text-center py-3">
									<h4 class="mb-0">{{ $group['total_rates'] }}</h4>
									<small>Total ratings</small>
								</div>
							</div>
						</div>
						<div class="col-6 col-md-3 mb-3">
							<div class="card bg-success text-white h-100">
								<div class="card-body text-center py-3">
									<h4 class="mb-0">{{ $group['high_count'] }}</h4>
									<small>High (≥ {{ $filters['highThreshold'] }}★)</small>
								</div>
							</div>
						</div>
						<div class="col-6 col-md-3 mb-3">
							<div class="card bg-danger text-white h-100">
								<div class="card-body text-center py-3">
									<h4 class="mb-0">{{ $group['low_count'] }}</h4>
									<small>Low (≤ {{ $filters['lowThreshold'] }}★)</small>
								</div>
							</div>
						</div>
						<div class="col-6 col-md-3 mb-3">
							<div class="card bg-warning text-white h-100">
								<div class="card-body text-center py-3">
									<h4 class="mb-0">{{ $group['avg_rate'] }}</h4>
									<small>Average given</small>
								</div>
							</div>
						</div>
					</div>

					<!-- Why flagged -->
					<div class="alert alert-warning mb-0">
						<strong><i class="gd-info-circle"></i> Why flagged:</strong>
						This user submitted {{ $group['high_count'] }} high rating{{ $group['high_count'] === 1 ? '' : 's' }}
						and {{ $group['low_count'] }} low rating{{ $group['low_count'] === 1 ? '' : 's' }}, producing
						<strong>{{ $group['pair_count'] }}</strong> conflicting pair{{ $group['pair_count'] === 1 ? '' : 's' }} on competing services
						@if($group['affected_categories']->count() > 0)
							in categor{{ $group['affected_categories']->count() === 1 ? 'y' : 'ies' }}
							<em>{{ $group['affected_categories']->implode(', ') }}</em>
						@endif
						@if($group['affected_cities']->count() > 0)
							across {{ $group['affected_cities']->implode(', ') }}
						@endif
						within {{ $filters['windowDays'] }} day{{ $filters['windowDays'] === 1 ? '' : 's' }}.
					</div>
				</div>

				<!-- Chart -->
				<div class="col-md-5">
					<div class="card h-100">
						<div class="card-header py-2">
							<small class="text-muted">Rating distribution (all this user's ratings)</small>
						</div>
						<div class="card-body">
							<canvas id="chart-user-{{ $user->id }}" height="180"
								data-distribution='@json(array_values($group['distribution']))'></canvas>
						</div>
					</div>
				</div>
			</div>

			<!-- Suspicious pairs table -->
			<h6 class="mb-3">Suspicious pairs ({{ $group['pair_count'] }})</h6>
			<div class="table-responsive">
				<table class="table table-sm align-middle mb-0">
					<thead>
						<tr>
							<th>City</th>
							<th>Shared categories</th>
							<th>High-rated service</th>
							<th>Low-rated service</th>
							<th>Gap</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						@foreach($group['pairs'] as $pair)
						<tr>
							<td>{{ $pair['city']->name ?? '—' }}</td>
							<td>
								@foreach($pair['categories'] as $cat)
									<span class="badge badge-secondary mr-1 mb-1">{{ $cat->name }}</span>
								@endforeach
							</td>
							<td>
								<a href="{{ route('service.show', $pair['high']->service) }}"><strong>{{ $pair['high']->service->name }}</strong></a>
								<div class="text-warning small">
									@for($i = 1; $i <= 5; $i++){{ $i <= $pair['high']->rate ? '★' : '☆' }}@endfor
									<span class="text-muted ml-1">({{ $pair['high']->rate }}/5)</span>
								</div>
								<small class="text-muted">{{ $pair['high']->created_at->format('M d, Y \a\t g:i A') }}</small>
							</td>
							<td>
								<a href="{{ route('service.show', $pair['low']->service) }}"><strong>{{ $pair['low']->service->name }}</strong></a>
								<div class="text-warning small">
									@for($i = 1; $i <= 5; $i++){{ $i <= $pair['low']->rate ? '★' : '☆' }}@endfor
									<span class="text-muted ml-1">({{ $pair['low']->rate }}/5)</span>
								</div>
								<small class="text-muted">{{ $pair['low']->created_at->format('M d, Y \a\t g:i A') }}</small>
							</td>
							<td>{{ $pair['days'] }}d</td>
							<td class="text-nowrap">
								<button type="button" class="btn btn-sm btn-outline-danger mb-1" title="Delete the high rating"
									onclick="deletePairRate({{ $pair['high']->service_id }}, {{ $pair['high']->id }}, this)">
									Del high
								</button>
								<button type="button" class="btn btn-sm btn-outline-danger mb-1" title="Delete the low rating"
									onclick="deletePairRate({{ $pair['low']->service_id }}, {{ $pair['low']->id }}, this)">
									Del low
								</button>
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
		</div>
	</div>
	@endforeach

	{{ $groups->links('components.pagination') }}
@endif

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function deletePairRate(serviceId, rateId, btn) {
	if (!confirm('Delete this rating? This action cannot be undone.')) return;

	$.ajax({
		url: `{{ url('') }}/services/${serviceId}/rates/${rateId}`,
		type: 'DELETE',
		headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
		dataType: 'json',
		success: function (data) {
			if (data.success) {
				$(btn).closest('tr').fadeOut(200, function () { $(this).remove(); });
			} else {
				alert(data.message || 'Error deleting rating');
			}
		},
		error: function (xhr) {
			alert((xhr.responseJSON && xhr.responseJSON.message) || 'Error deleting rating');
		}
	});
}

function initChart(canvas) {
	if (canvas.dataset.chartInited) return;
	canvas.dataset.chartInited = '1';
	var dist = $(canvas).data('distribution');
	new Chart(canvas.getContext('2d'), {
		type: 'bar',
		data: {
			labels: ['1★', '2★', '3★', '4★', '5★'],
			datasets: [{
				label: 'Count',
				data: dist,
				backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#28a745'],
				borderWidth: 0
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: { legend: { display: false } },
			scales: {
				y: { beginAtZero: true, ticks: { precision: 0 } }
			}
		}
	});
}

$(function () {
	$('.collapse').on('shown.bs.collapse', function () {
		$(this).find('canvas[id^="chart-user-"]').each(function () { initChart(this); });
		$(this).prev('.card-header').find('.collapse-indicator').removeClass('gd-angle-down').addClass('gd-angle-up');
	});
	$('.collapse').on('hidden.bs.collapse', function () {
		$(this).prev('.card-header').find('.collapse-indicator').removeClass('gd-angle-up').addClass('gd-angle-down');
	});
});
</script>
@endsection
