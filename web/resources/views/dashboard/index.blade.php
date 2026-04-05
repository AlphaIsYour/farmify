@extends('layouts.app')
@section('title', 'Overview')
@section('breadcrumb', 'Overview')

@section('content')

{{-- Page header --}}
<div class="page-header flex-between">
  <div>
    <h1 class="page-title">System Overview</h1>
    <p class="page-subtitle">Real-time monitoring — last updated <span id="last-updated">just now</span></p>
  </div>
  <button class="btn btn-primary" id="refresh-btn">
    <i class="ri-refresh-line"></i> Refresh
  </button>
</div>

{{-- Stat cards --}}
<div class="stat-grid" id="stat-grid">
  <div class="stat-card" style="--stat-color:var(--primary);--stat-bg:var(--primary-pale)">
    <div class="stat-icon"><i class="ri-cpu-line"></i></div>
    <div class="stat-label">Devices Online</div>
    <div class="stat-value">{{ $stats['devices_online'] }}<span style="font-size:1rem;color:var(--text-3)">/{{ $stats['devices_total'] }}</span></div>
    <div class="stat-meta">
      <span class="status-dot online"></span>
      <span class="text-xs text-muted">{{ $stats['devices_online'] }} active now</span>
    </div>
  </div>

  <div class="stat-card" style="--stat-color:#1F618D;--stat-bg:var(--info-bg)">
    <div class="stat-icon"><i class="ri-drop-line"></i></div>
    <div class="stat-label">Avg Soil Moisture</div>
    <div class="stat-value">{{ number_format($stats['avg_moisture'], 1) }}<span style="font-size:1rem;color:var(--text-3)">%</span></div>
    <div class="stat-meta">
      <span class="stat-delta {{ $stats['moisture_trend'] >= 0 ? 'up' : 'down' }}">
        <i class="ri-arrow-{{ $stats['moisture_trend'] >= 0 ? 'up' : 'down' }}-line"></i>
        {{ abs($stats['moisture_trend']) }}%
      </span>
      <span class="text-xs text-muted">vs last hour</span>
    </div>
  </div>

  <div class="stat-card" style="--stat-color:var(--warning);--stat-bg:var(--warning-bg)">
    <div class="stat-icon"><i class="ri-water-flash-line"></i></div>
    <div class="stat-label">Active Pumps</div>
    <div class="stat-value">{{ $stats['active_pumps'] }}</div>
    <div class="stat-meta">
      <span class="status-dot running" style="background:var(--accent)"></span>
      <span class="text-xs text-muted">running now</span>
    </div>
  </div>

  <div class="stat-card" style="--stat-color:var(--success);--stat-bg:var(--success-bg)">
    <div class="stat-icon"><i class="ri-remote-control-line"></i></div>
    <div class="stat-label">Commands Today</div>
    <div class="stat-value">{{ $stats['commands_today'] }}</div>
    <div class="stat-meta">
      <span class="text-xs" style="color:var(--success)">
        <i class="ri-checkbox-circle-line"></i> {{ $stats['commands_success'] }} success
      </span>
    </div>
  </div>
</div>

{{-- Row 1: moisture chart + device status --}}
<div class="grid-2-1 row-gap">

  {{-- Moisture trend chart --}}
  <div class="card">
    <div class="card-header">
      <i class="ri-line-chart-line" style="color:var(--primary)"></i>
      <span class="card-title">Soil Moisture Trend</span>
      <div class="flex gap-2">
        <select class="form-select" style="height:28px;font-size:0.75rem;width:auto" id="chart-range">
          <option value="1h">Last 1 hour</option>
          <option value="6h" selected>Last 6 hours</option>
          <option value="24h">Last 24 hours</option>
          <option value="7d">Last 7 days</option>
        </select>
      </div>
    </div>
    <div class="card-body">
      <div class="chart-canvas-wrap" style="height:220px">
        <canvas id="moisture-chart"></canvas>
      </div>
    </div>
  </div>

  {{-- Device status cards --}}
  <div class="card">
    <div class="card-header">
      <i class="ri-cpu-line" style="color:var(--primary)"></i>
      <span class="card-title">Device Status</span>
      <span class="badge badge-success">{{ $stats['devices_online'] }} online</span>
    </div>
    <div class="card-body" style="padding:12px 16px;display:flex;flex-direction:column;gap:10px">
      @foreach($devices as $device)
      <div style="display:flex;align-items:center;gap:12px;padding:10px 12px;background:var(--bg-page);border-radius:var(--r-md);border:1px solid var(--border-3)">
        <div class="status-dot {{ $device->status }}"></div>
        <div style="flex:1;min-width:0">
          <div style="font-size:0.8125rem;font-weight:500;color:var(--text-1)">{{ $device->zone_name }}</div>
          <div style="font-size:0.6875rem;color:var(--text-3)">{{ $device->device_code }}</div>
        </div>
        <div style="text-align:right">
          <div style="font-family:var(--font-h);font-size:0.9375rem;font-weight:600;color:var(--text-1)">
            {{ $device->latestSensor ? number_format($device->latestSensor->soil_moisture, 1) : '—' }}<span style="font-size:0.6875rem;color:var(--text-3)">%</span>
          </div>
          <div>
            @php $pump = $device->pumpStatus?->status ?? 'idle'; @endphp
            <span class="badge badge-{{ $pump === 'running' ? 'accent' : ($pump === 'stopped' ? 'gray' : 'gray') }}">
              <i class="ri-water-flash-line"></i> {{ ucfirst($pump) }}
            </span>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>

{{-- Row 2: commands bar chart + command distribution doughnut --}}
<div class="grid-1-1 row-gap">

  <div class="card">
    <div class="card-header">
      <i class="ri-bar-chart-2-line" style="color:var(--primary)"></i>
      <span class="card-title">Commands Per Day</span>
      <span class="text-xs text-muted">Last 7 days</span>
    </div>
    <div class="card-body">
      <div class="chart-canvas-wrap" style="height:180px">
        <canvas id="cmd-bar-chart"></canvas>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <i class="ri-pie-chart-2-line" style="color:var(--primary)"></i>
      <span class="card-title">Command Distribution</span>
    </div>
    <div class="card-body" style="display:flex;align-items:center;gap:20px">
      <div class="chart-canvas-wrap" style="height:160px;width:160px;flex-shrink:0">
        <canvas id="status-doughnut"></canvas>
      </div>
      <div style="flex:1">
        <div style="display:flex;flex-direction:column;gap:8px">
          @foreach([['Done','var(--primary)'],['Pending','var(--accent)'],['Failed','var(--danger)']] as [$label,$color])
          <div style="display:flex;align-items:center;gap:8px">
            <div style="width:8px;height:8px;border-radius:50%;background:{{ $color }};flex-shrink:0"></div>
            <span style="font-size:0.75rem;color:var(--text-2);flex:1">{{ $label }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Row 3: recent commands + activity log --}}
<div class="grid-1-1 row-gap">

  {{-- Recent commands --}}
  <div class="card">
    <div class="card-header">
      <i class="ri-remote-control-line" style="color:var(--primary)"></i>
      <span class="card-title">Recent Commands</span>
      <a href="{{ route('dashboard.commands') }}" class="btn btn-ghost btn-sm">View all <i class="ri-arrow-right-line"></i></a>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Device</th>
            <th>Type</th>
            <th>Source</th>
            <th>Status</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentCommands as $cmd)
          <tr>
            <td>
              <span style="font-weight:500">{{ $cmd->device->device_code }}</span>
            </td>
            <td>
              <span class="badge {{ $cmd->command_type === 'start_pump' ? 'badge-success' : 'badge-gray' }}">
                <i class="ri-{{ $cmd->command_type === 'start_pump' ? 'play' : 'stop' }}-line"></i>
                {{ $cmd->command_type === 'start_pump' ? 'Start' : 'Stop' }}
              </span>
            </td>
            <td>
              <span class="badge {{ $cmd->source === 'auto' ? 'badge-accent' : 'badge-info' }}">
                {{ ucfirst($cmd->source) }}
              </span>
            </td>
            <td>
              <span class="badge badge-{{ match($cmd->status) { 'done'=>'success','failed'=>'danger','pending'=>'warning','processing'=>'info',default=>'gray' } }}">
                {{ ucfirst($cmd->status) }}
              </span>
            </td>
            <td class="td-muted">{{ $cmd->created_at->diffForHumans() }}</td>
          </tr>
          @empty
          <tr><td colspan="5"><div class="empty-state"><i class="ri-inbox-line"></i><span class="empty-title">No commands yet</span></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Activity log --}}
  <div class="card">
    <div class="card-header">
      <i class="ri-file-list-3-line" style="color:var(--primary)"></i>
      <span class="card-title">Activity Log</span>
      <a href="{{ route('dashboard.logs') }}" class="btn btn-ghost btn-sm">View all <i class="ri-arrow-right-line"></i></a>
    </div>
    <div class="card-body" style="padding:8px 16px">
      <div class="log-list">
        @forelse($recentLogs as $log)
        @php
          $dotClass = match(true) {
            str_contains($log->action,'AUTO') => 'warning',
            str_contains($log->action,'STOP') => 'danger',
            str_contains($log->action,'START'), str_contains($log->action,'DONE') => 'success',
            default => ''
          };
          $icon = match(true) {
            str_contains($log->action,'AUTO')   => 'ri-robot-line',
            str_contains($log->action,'PUMP')   => 'ri-water-flash-line',
            str_contains($log->action,'COMMAND')=> 'ri-remote-control-line',
            default => 'ri-file-list-line'
          };
        @endphp
        <div class="log-item">
          <div class="log-dot {{ $dotClass }}"><i class="{{ $icon }}"></i></div>
          <div class="log-content">
            <div class="log-action">{{ $log->action }}</div>
            <div class="log-desc">{{ Str::limit($log->description, 72) }}</div>
            <div class="log-time">{{ $log->device->device_code }} · {{ $log->created_at->diffForHumans() }}</div>
          </div>
        </div>
        @empty
        <div class="empty-state"><i class="ri-inbox-line"></i><span class="empty-title">No activity yet</span></div>
        @endforelse
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Moisture trend chart
  const labels = @json($chartData['labels']);
  const datasets = @json($chartData['datasets']);
  const mc = buildMoistureChart('moisture-chart', labels, datasets);
  window._charts = [mc];

  // Commands bar chart
  const bc = buildCommandsChart('cmd-bar-chart', @json($cmdChart['labels']), @json($cmdChart['data']));
  window._charts.push(bc);

  // Status doughnut
  const dc = buildStatusChart('status-doughnut',
    @json($cmdDist['data']),
    @json($cmdDist['labels']),
    ['#2D6A4F', '#F4C430', '#B03A2E']
  );
  window._charts.push(dc);

  // Chart range selector
  document.getElementById('chart-range').addEventListener('change', function() {
    TopBar.refresh(async () => {
      const res = await fetch(`/dashboard/chart-data?range=${this.value}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await res.json();
      mc.data.labels = data.labels;
      mc.data.datasets.forEach((ds, i) => { if (data.datasets[i]) ds.data = data.datasets[i].data; });
      mc.update();
      document.getElementById('last-updated').textContent = 'just now';
    });
  });

  // Manual refresh
  document.getElementById('refresh-btn').addEventListener('click', () => {
    TopBar.refresh(async () => {
      await new Promise(r => setTimeout(r, 1000));
      window.location.reload();
    });
  });

  // Auto-poll device statuses every 15s
  new DataPoller('/dashboard/api/devices', data => {
    const offlineCount = data.filter(d => d.status === 'offline').length;
    const badge = document.getElementById('sb-offline-count');
    if (badge) {
      badge.style.display = offlineCount > 0 ? '' : 'none';
      badge.textContent = offlineCount;
    }
    document.getElementById('last-updated').textContent = 'just now';
  }, 15000).start();
});
</script>
@endpush