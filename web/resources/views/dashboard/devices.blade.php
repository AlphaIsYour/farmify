@extends('layouts.app')
@section('title', 'Devices')
@section('breadcrumb', 'Devices')

@section('content')

<div class="page-header flex-between">
  <div>
    <h1 class="page-title">Devices</h1>
    <p class="page-subtitle">{{ $devices->total() }} device(s) registered</p>
  </div>
  <div class="flex gap-2">
    <button class="btn btn-ghost" id="refresh-devices" data-tip="Refresh">
      <i class="ri-refresh-line"></i>
    </button>
    <button class="btn btn-primary" data-modal-open="modal-add-device">
      <i class="ri-add-line"></i> Add Device
    </button>
  </div>
</div>

{{-- Filter bar --}}
<div class="card row-gap" style="padding:14px 18px">
  <div class="filter-row">
    <select class="form-select" style="width:160px" id="filter-status">
      <option value="">All Status</option>
      <option value="online">Online</option>
      <option value="offline">Offline</option>
    </select>
    <select class="form-select" style="width:160px" id="filter-pump">
      <option value="">All Pump Status</option>
      <option value="idle">Idle</option>
      <option value="running">Running</option>
      <option value="stopped">Stopped</option>
    </select>
    <div class="topbar-search" style="flex:1;max-width:280px">
      <i class="ri-search-line"></i>
      <input type="text" placeholder="Search device code or zone..." id="search-device">
    </div>
    <button class="btn btn-ghost btn-sm" id="clear-filter">
      <i class="ri-close-line"></i> Clear
    </button>
  </div>
</div>

{{-- Device carousel (summary) --}}
<div class="card row-gap" style="overflow:visible">
  <div class="card-header">
    <i class="ri-layout-grid-line" style="color:var(--primary)"></i>
    <span class="card-title">Zone Overview</span>
  </div>
  <div class="card-body" style="padding:12px 16px">
    <div class="carousel" id="zone-carousel" style="position:relative">
      <button class="carousel-nav carousel-prev"><i class="ri-arrow-left-s-line"></i></button>
      <div class="carousel-track">
        @foreach($devices as $device)
        <div class="carousel-slide" style="padding:0 40px">
          <div class="device-card">
            <div class="device-card-header">
              <div>
                <div class="device-card-name">{{ $device->zone_name }}</div>
                <div class="device-card-code">{{ $device->device_code }}</div>
              </div>
              <div class="flex gap-2">
                <span class="badge badge-{{ $device->status === 'online' ? 'success' : 'gray' }}">
                  <span class="status-dot {{ $device->status }}" style="width:6px;height:6px"></span>
                  {{ ucfirst($device->status) }}
                </span>
                <div class="dropdown">
                  <button class="btn btn-ghost btn-sm btn-icon-only" data-dropdown="dev-menu-{{ $device->id }}">
                    <i class="ri-more-2-line"></i>
                  </button>
                  <div class="dropdown-menu" id="dev-menu-{{ $device->id }}">
                    <div class="dropdown-item" onclick="sendCommand({{ $device->id }},'start_pump')">
                      <i class="ri-play-line"></i> Start Pump
                    </div>
                    <div class="dropdown-item" onclick="sendCommand({{ $device->id }},'stop_pump')">
                      <i class="ri-stop-line"></i> Stop Pump
                    </div>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-item" onclick="Modal.open('modal-detail-{{ $device->id }}')">
                      <i class="ri-eye-line"></i> View Details
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <div class="device-metric-label">Soil Moisture</div>
              <div style="display:flex;align-items:flex-end;gap:8px;margin-bottom:6px">
                <span class="device-metric-val">{{ $device->latestSensor ? number_format($device->latestSensor->soil_moisture, 1) : '—' }}</span>
                <span class="device-metric-unit">%</span>
                @if($device->latestSensor && $device->latestSensor->soil_moisture < 30)
                  <span class="badge badge-danger"><i class="ri-alert-line"></i> Low</span>
                @elseif($device->latestSensor && $device->latestSensor->soil_moisture > 70)
                  <span class="badge badge-success"><i class="ri-check-line"></i> Good</span>
                @endif
              </div>
              <div class="progress">
                <div class="progress-bar" style="width:{{ $device->latestSensor ? $device->latestSensor->soil_moisture : 0 }}%;
                  background:{{ ($device->latestSensor?->soil_moisture ?? 50) < 30 ? 'var(--danger)' : 'var(--primary)' }}">
                </div>
              </div>
            </div>

            @if($device->latestSensor)
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
              <div style="background:var(--bg-page);border-radius:var(--r-md);padding:8px 10px;border:1px solid var(--border-3)">
                <div class="device-metric-label"><i class="ri-temp-hot-line"></i> Temperature</div>
                <div style="font-family:var(--font-h);font-size:1rem;font-weight:600;color:var(--text-1)">
                  {{ number_format($device->latestSensor->temperature, 1) }}<span style="font-size:0.6875rem;color:var(--text-3)">°C</span>
                </div>
              </div>
              <div style="background:var(--bg-page);border-radius:var(--r-md);padding:8px 10px;border:1px solid var(--border-3)">
                <div class="device-metric-label"><i class="ri-contrast-drop-line"></i> Humidity</div>
                <div style="font-family:var(--font-h);font-size:1rem;font-weight:600;color:var(--text-1)">
                  {{ number_format($device->latestSensor->humidity, 1) }}<span style="font-size:0.6875rem;color:var(--text-3)">%</span>
                </div>
              </div>
            </div>
            @endif

            <div class="device-footer">
              <div class="device-pump-row">
                <i class="ri-water-flash-line" style="color:var(--text-3);font-size:14px"></i>
                @php $pump = $device->pumpStatus?->status ?? 'idle'; @endphp
                <span class="badge badge-{{ $pump === 'running' ? 'accent' : 'gray' }}">
                  Pump: {{ ucfirst($pump) }}
                </span>
              </div>
              <span class="device-last-seen">
                <i class="ri-time-line"></i>
                {{ $device->last_seen ? $device->last_seen->diffForHumans() : 'Never' }}
              </span>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      <button class="carousel-nav carousel-next"><i class="ri-arrow-right-s-line"></i></button>
      <div class="carousel-dots">
        @foreach($devices as $i => $d)
          <div class="carousel-dot {{ $i === 0 ? 'active' : '' }}"></div>
        @endforeach
      </div>
    </div>
  </div>
</div>

{{-- Devices table --}}
<div class="card">
  <div class="card-header">
    <i class="ri-list-check-3" style="color:var(--primary)"></i>
    <span class="card-title">All Devices</span>
    <span class="badge badge-gray">{{ $devices->total() }} total</span>
  </div>
  <div class="table-wrap" id="devices-table">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Device Code</th>
          <th>Zone Name</th>
          <th>Status</th>
          <th>Moisture</th>
          <th>Pump</th>
          <th>Last Seen</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="devices-tbody">
        @forelse($devices as $device)
        <tr>
          <td class="td-muted">{{ $device->id }}</td>
          <td class="td-mono">{{ $device->device_code }}</td>
          <td><span style="font-weight:500">{{ $device->zone_name }}</span></td>
          <td>
            <div style="display:flex;align-items:center;gap:6px">
              <span class="status-dot {{ $device->status }}"></span>
              <span class="text-sm">{{ ucfirst($device->status) }}</span>
            </div>
          </td>
          <td>
            @if($device->latestSensor)
              <div style="display:flex;align-items:center;gap:8px">
                <span style="font-family:var(--font-h);font-weight:600">{{ number_format($device->latestSensor->soil_moisture,1) }}%</span>
                <div class="progress" style="width:60px"><div class="progress-bar" style="width:{{ $device->latestSensor->soil_moisture }}%"></div></div>
              </div>
            @else
              <span class="td-muted">—</span>
            @endif
          </td>
          <td>
            @php $pump = $device->pumpStatus?->status ?? 'idle'; @endphp
            <span class="badge badge-{{ $pump === 'running' ? 'accent' : 'gray' }}">{{ ucfirst($pump) }}</span>
          </td>
          <td class="td-muted">{{ $device->last_seen ? $device->last_seen->diffForHumans() : '—' }}</td>
          <td>
            <div class="flex gap-2">
              <button class="btn btn-ghost btn-sm btn-icon-only" data-tip="Start pump" onclick="sendCommand({{ $device->id }},'start_pump')">
                <i class="ri-play-line" style="color:var(--success)"></i>
              </button>
              <button class="btn btn-ghost btn-sm btn-icon-only" data-tip="Stop pump" onclick="sendCommand({{ $device->id }},'stop_pump')">
                <i class="ri-stop-line" style="color:var(--danger)"></i>
              </button>
              <button class="btn btn-ghost btn-sm btn-icon-only" data-tip="View details" onclick="Modal.open('modal-detail-{{ $device->id }}')">
                <i class="ri-eye-line"></i>
              </button>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="8">
          <div class="empty-state">
            <i class="ri-cpu-line"></i>
            <span class="empty-title">No devices found</span>
            <span class="empty-sub">Devices will appear here once they connect</span>
          </div>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($devices->hasPages())
  <div class="card-footer flex-between">
    <span class="text-xs text-muted">Showing {{ $devices->firstItem() }}–{{ $devices->lastItem() }} of {{ $devices->total() }}</span>
    {{ $devices->links('dashboard.partials.pagination') }}
  </div>
  @endif
</div>

@endsection

{{-- Device detail modals --}}
@push('modals')
@foreach($devices as $device)
<div class="modal-overlay" id="modal-detail-{{ $device->id }}">
  <div class="modal">
    <div class="modal-header">
      <i class="ri-cpu-line" style="color:var(--primary)"></i>
      <span class="modal-title">{{ $device->zone_name }} — {{ $device->device_code }}</span>
      <button class="modal-close" data-modal-close="modal-detail-{{ $device->id }}"><i class="ri-close-line"></i></button>
    </div>
    <div class="modal-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div class="form-group">
          <label class="form-label">Status</label>
          <span class="badge badge-{{ $device->status === 'online' ? 'success' : 'gray' }}" style="align-self:flex-start">{{ ucfirst($device->status) }}</span>
        </div>
        <div class="form-group">
          <label class="form-label">Pump Status</label>
          <span class="badge badge-{{ ($device->pumpStatus?->status ?? 'idle') === 'running' ? 'accent' : 'gray' }}" style="align-self:flex-start">{{ ucfirst($device->pumpStatus?->status ?? 'idle') }}</span>
        </div>
        @if($device->latestSensor)
        <div class="form-group">
          <label class="form-label">Soil Moisture</label>
          <span style="font-family:var(--font-h);font-size:1.25rem;font-weight:600;color:var(--text-1)">{{ number_format($device->latestSensor->soil_moisture,1) }}%</span>
        </div>
        <div class="form-group">
          <label class="form-label">Temperature</label>
          <span style="font-family:var(--font-h);font-size:1.25rem;font-weight:600;color:var(--text-1)">{{ number_format($device->latestSensor->temperature,1) }}°C</span>
        </div>
        @endif
        <div class="form-group">
          <label class="form-label">Last Seen</label>
          <span class="text-sm">{{ $device->last_seen ? $device->last_seen->format('d M Y, H:i') : '—' }}</span>
        </div>
        <div class="form-group">
          <label class="form-label">Location</label>
          <span class="text-sm">{{ $device->location ?? 'Not set' }}</span>
        </div>
      </div>
      <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border-2)">
        <div class="form-label" style="margin-bottom:8px">Moisture Level</div>
        <div class="progress" style="height:8px">
          <div class="progress-bar" style="width:{{ $device->latestSensor?->soil_moisture ?? 0 }}%;background:{{ ($device->latestSensor?->soil_moisture ?? 50) < 30 ? 'var(--danger)' : 'var(--primary)' }}"></div>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:4px">
          <span class="text-xs text-muted">0%</span>
          <span class="text-xs text-muted">100%</span>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-modal-close="modal-detail-{{ $device->id }}">Close</button>
      <button class="btn btn-primary" onclick="sendCommand({{ $device->id }},'start_pump');Modal.close('modal-detail-{{ $device->id }}')">
        <i class="ri-play-line"></i> Start Pump
      </button>
    </div>
  </div>
</div>
@endforeach

{{-- Add device modal --}}
<div class="modal-overlay" id="modal-add-device">
  <div class="modal">
    <div class="modal-header">
      <i class="ri-add-circle-line" style="color:var(--primary)"></i>
      <span class="modal-title">Add New Device</span>
      <button class="modal-close" data-modal-close="modal-add-device"><i class="ri-close-line"></i></button>
    </div>
    <form action="{{ route('dashboard.devices.store') }}" method="POST">
      @csrf
      <div class="modal-body">
        <div style="display:flex;flex-direction:column;gap:14px">
          <div class="form-group">
            <label class="form-label">Device Code</label>
            <input type="text" class="form-input" name="device_code" placeholder="e.g. ZONE-D" required>
          </div>
          <div class="form-group">
            <label class="form-label">Zone Name</label>
            <input type="text" class="form-input" name="zone_name" placeholder="e.g. Lahan Kedelai" required>
          </div>
          <div class="form-group">
            <label class="form-label">Location (optional)</label>
            <input type="text" class="form-input" name="location" placeholder="e.g. Blok A, Greenhouse 1">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close="modal-add-device">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="ri-add-line"></i> Add Device</button>
      </div>
    </form>
  </div>
</div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  @if(session('success'))
    Toast.show('success', 'Success', '{{ session('success') }}');
  @endif

  const tbody = document.getElementById('devices-tbody');
  const statusFilter = document.getElementById('filter-status');
  const pumpFilter   = document.getElementById('filter-pump');
  const searchInput  = document.getElementById('search-device');

  function filterTable() {
    const s = statusFilter.value.toLowerCase();
    const p = pumpFilter.value.toLowerCase();
    const q = searchInput.value.toLowerCase();
    tbody.querySelectorAll('tr').forEach(row => {
      const text = row.textContent.toLowerCase();
      const show = (!s || text.includes(s)) && (!p || text.includes(p)) && (!q || text.includes(q));
      row.style.display = show ? '' : 'none';
    });
  }
  statusFilter.addEventListener('change', filterTable);
  pumpFilter.addEventListener('change', filterTable);
  searchInput.addEventListener('input', filterTable);
  document.getElementById('clear-filter').addEventListener('click', () => {
    statusFilter.value = ''; pumpFilter.value = ''; searchInput.value = '';
    filterTable();
  });

  document.getElementById('refresh-devices').addEventListener('click', () => {
    TopBar.refresh(() => new Promise(r => setTimeout(() => { window.location.reload(); r(); }, 3000)));
  });
});
</script>
@endpush