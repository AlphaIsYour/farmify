@extends('layouts.app')
@section('title', 'Activity Log')
@section('breadcrumb', 'Activity Log')

@section('content')

<div class="page-header flex-between">
  <div>
    <h1 class="page-title">Activity Log</h1>
    <p class="page-subtitle">Complete system event history</p>
  </div>
  <button class="btn btn-ghost" onclick="TopBar.refresh(() => new Promise(r => setTimeout(() => { window.location.reload(); r(); }, 1000)))">
    <i class="ri-refresh-line"></i> Refresh
  </button>
</div>

<div class="card row-gap" style="padding:14px 18px">
  <div class="filter-row">
    <select class="form-select" style="width:150px" id="f-device">
      <option value="">All Devices</option>
      @foreach($devices as $d)
        <option value="{{ $d->id }}" {{ request('device_id') == $d->id ? 'selected' : '' }}>{{ $d->device_code }}</option>
      @endforeach
    </select>
    <select class="form-select" style="width:160px" id="f-action">
      <option value="">All Actions</option>
      <option value="COMMAND_SENT">Command Sent</option>
      <option value="AUTO_COMMAND">Auto Command</option>
      <option value="PUMP_STATUS_UPDATE">Pump Update</option>
    </select>
    <input type="date" class="form-input" style="width:160px" id="f-date" value="{{ request('date') }}">
    <button class="btn btn-ghost btn-sm" id="apply-log-filter"><i class="ri-filter-line"></i> Apply</button>
    <button class="btn btn-ghost btn-sm" onclick="window.location='{{ route('dashboard.logs') }}'">
      <i class="ri-close-line"></i> Clear
    </button>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px" class="row-gap">
  @foreach([
    ['Total Events', $logStats['total'],   'ri-list-check-3', 'var(--info)'],
    ['Auto Triggers',$logStats['auto'],    'ri-robot-line',   'var(--accent)'],
    ['Pump Actions', $logStats['pump'],    'ri-water-flash-line','var(--primary)'],
  ] as [$label,$val,$icon,$color])
  <div style="background:var(--bg-surface);border-radius:var(--r-lg);padding:14px 18px;border:1px solid var(--border-2);box-shadow:var(--sh-card);display:flex;align-items:center;gap:14px">
    <i class="{{ $icon }}" style="font-size:22px;color:{{ $color }}"></i>
    <div>
      <div style="font-family:var(--font-h);font-size:1.25rem;font-weight:600;color:var(--text-1)">{{ $val }}</div>
      <div style="font-size:0.75rem;color:var(--text-3)">{{ $label }}</div>
    </div>
  </div>
  @endforeach
</div>

<div class="card">
  <div class="card-header">
    <i class="ri-timeline-view" style="color:var(--primary)"></i>
    <span class="card-title">Events</span>
    <span class="badge badge-gray">{{ $logs->total() }} total</span>
  </div>
  <div class="card-body">
    @if($logs->isEmpty())
      <div class="empty-state">
        <i class="ri-file-list-3-line"></i>
        <span class="empty-title">No activity found</span>
        <span class="empty-sub">Logs will appear here once devices start sending data</span>
      </div>
    @else
      <div class="log-list">
        @foreach($logs as $log)
        @php
          $dotClass = match(true) {
            str_contains($log->action,'AUTO')    => 'warning',
            str_contains($log->action,'STOP')    => 'danger',
            str_contains($log->action,'DONE'), str_contains($log->action,'START') => 'success',
            default => ''
          };
          $icon = match(true) {
            str_contains($log->action,'AUTO')    => 'ri-robot-line',
            str_contains($log->action,'PUMP')    => 'ri-water-flash-line',
            str_contains($log->action,'COMMAND') => 'ri-remote-control-line',
            default                              => 'ri-file-list-line'
          };
        @endphp
        <div class="log-item">
          <div class="log-dot {{ $dotClass }}"><i class="{{ $icon }}"></i></div>
          <div class="log-content">
            <div class="flex-between">
              <span class="log-action">{{ $log->action }}</span>
              @if($log->command)
                <span class="badge badge-{{ match($log->command->status) {'done'=>'success','failed'=>'danger','pending'=>'warning',default=>'gray'} }}">
                  cmd #{{ $log->command_id }}
                </span>
              @endif
            </div>
            <div class="log-desc">{{ $log->description }}</div>
            <div class="log-time">
              <i class="ri-cpu-line"></i> {{ $log->device->device_code }}
              &nbsp;·&nbsp;
              <i class="ri-time-line"></i> {{ $log->created_at->format('d M Y, H:i:s') }}
              &nbsp;·&nbsp;
              {{ $log->created_at->diffForHumans() }}
            </div>
          </div>
        </div>
        @endforeach
      </div>
    @endif
  </div>
  @if($logs->hasPages())
  <div class="card-footer flex-between">
    <span class="text-xs text-muted">{{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}</span>
    {{ $logs->appends(request()->query())->links('dashboard.partials.pagination') }}
  </div>
  @endif
</div>

@endsection

@push('scripts')
<script>
document.getElementById('apply-log-filter').addEventListener('click', () => {
  const params = new URLSearchParams();
  const d = document.getElementById('f-device').value;
  const a = document.getElementById('f-action').value;
  const dt= document.getElementById('f-date').value;
  if (d)  params.set('device_id', d);
  if (a)  params.set('action', a);
  if (dt) params.set('date', dt);
  window.location = '{{ route("dashboard.logs") }}?' + params.toString();
});
</script>
@endpush