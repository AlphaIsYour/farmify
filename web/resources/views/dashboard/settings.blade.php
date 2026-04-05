@extends('layouts.app')
@section('title', 'Settings')
@section('breadcrumb', 'Settings')

@section('content')

<div class="page-header">
  <h1 class="page-title">Settings</h1>
  <p class="page-subtitle">System configuration and API key management</p>
</div>

{{-- Alert success/error --}}
@if(session('success'))
<script>document.addEventListener('DOMContentLoaded',()=>Toast.show('success','Saved','{{ session('success') }}'))</script>
@endif
@if(session('error'))
<script>document.addEventListener('DOMContentLoaded',()=>Toast.show('danger','Error','{{ session('error') }}'))</script>
@endif

<div class="grid-2-1" style="align-items:start">

  {{-- LEFT COLUMN --}}
  <div style="display:flex;flex-direction:column;gap:16px">

    {{-- Irrigation Threshold --}}
    <div class="card">
      <div class="card-header">
        <i class="ri-drop-line" style="color:var(--primary)"></i>
        <span class="card-title">Irrigation Threshold</span>
        <span class="badge badge-success">Active</span>
      </div>
      <form action="{{ route('dashboard.settings.threshold') }}" method="POST">
        @csrf
        <div class="card-body" style="display:flex;flex-direction:column;gap:16px">
          <div class="form-group">
            <label class="form-label">Auto-irrigation trigger (%)</label>
            <div style="display:flex;align-items:center;gap:12px">
              <input type="range" name="threshold_moisture" id="range-moisture"
                min="10" max="60" value="{{ $settings['threshold_moisture'] ?? 30 }}"
                style="flex:1;accent-color:var(--primary)"
                oninput="document.getElementById('range-moisture-val').textContent=this.value+'%'">
              <span id="range-moisture-val" style="font-family:var(--font-h);font-weight:600;font-size:0.9375rem;color:var(--text-1);min-width:40px;text-align:right">
                {{ $settings['threshold_moisture'] ?? 30 }}%
              </span>
            </div>
            <p style="font-size:0.75rem;color:var(--text-3);margin-top:4px">
              Pump will start automatically when soil moisture drops below this value.
            </p>
          </div>

          <div class="form-group">
            <label class="form-label">Stop irrigation when moisture reaches (%)</label>
            <div style="display:flex;align-items:center;gap:12px">
              <input type="range" name="threshold_stop" id="range-stop"
                min="40" max="100" value="{{ $settings['threshold_stop'] ?? 70 }}"
                style="flex:1;accent-color:var(--primary)"
                oninput="document.getElementById('range-stop-val').textContent=this.value+'%'">
              <span id="range-stop-val" style="font-family:var(--font-h);font-weight:600;font-size:0.9375rem;color:var(--text-1);min-width:40px;text-align:right">
                {{ $settings['threshold_stop'] ?? 70 }}%
              </span>
            </div>
          </div>

          <div style="padding:12px;background:var(--bg-page);border-radius:var(--r-md);border:1px solid var(--border-2)">
            <div style="font-size:0.75rem;color:var(--text-3);margin-bottom:8px;font-weight:500">Preview range</div>
            <div class="progress" style="height:10px">
              <div class="progress-bar danger" id="preview-bar" style="width:{{ $settings['threshold_moisture'] ?? 30 }}%"></div>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:6px">
              <span style="font-size:0.6875rem;color:var(--danger)">Trigger: <span id="preview-trigger">{{ $settings['threshold_moisture'] ?? 30 }}%</span></span>
              <span style="font-size:0.6875rem;color:var(--success)">Stop: <span id="preview-stop">{{ $settings['threshold_stop'] ?? 70 }}%</span></span>
            </div>
          </div>
        </div>
        <div class="card-footer" style="display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Save Threshold</button>
        </div>
      </form>
    </div>

    {{-- Worker Settings --}}
    <div class="card">
      <div class="card-header">
        <i class="ri-settings-4-line" style="color:var(--primary)"></i>
        <span class="card-title">Worker Configuration</span>
      </div>
      <form action="{{ route('dashboard.settings.worker') }}" method="POST">
        @csrf
        <div class="card-body" style="display:flex;flex-direction:column;gap:16px">
          <div class="form-group">
            <label class="form-label">Polling interval (seconds)</label>
            <input type="number" class="form-input" name="poll_interval"
              value="{{ $settings['poll_interval'] ?? 5 }}" min="1" max="60">
            <p style="font-size:0.75rem;color:var(--text-3);margin-top:4px">How often the worker checks for pending commands.</p>
          </div>
          <div class="form-group">
            <label class="form-label">Device timeout (seconds)</label>
            <input type="number" class="form-input" name="device_timeout"
              value="{{ $settings['device_timeout'] ?? 60 }}" min="10" max="600">
            <p style="font-size:0.75rem;color:var(--text-3);margin-top:4px">Mark device as offline if no data received within this period.</p>
          </div>
          <div class="form-group">
            <label class="form-label">Data ingest interval (seconds)</label>
            <input type="number" class="form-input" name="ingest_interval"
              value="{{ $settings['ingest_interval'] ?? 10 }}" min="5" max="300">
            <p style="font-size:0.75rem;color:var(--text-3);margin-top:4px">How often each device sends sensor data.</p>
          </div>

          <div style="display:flex;align-items:center;justify-content:space-between;padding:12px;background:var(--bg-page);border-radius:var(--r-md);border:1px solid var(--border-2)">
            <div>
              <div style="font-size:0.8125rem;font-weight:500;color:var(--text-1)">Auto-irrigation enabled</div>
              <div style="font-size:0.75rem;color:var(--text-3)">Allow system to trigger irrigation automatically</div>
            </div>
            <label class="toggle-wrap">
              <input type="checkbox" class="toggle-input" name="auto_irrigation"
                {{ ($settings['auto_irrigation'] ?? true) ? 'checked' : '' }}>
              <div class="toggle-track"></div>
            </label>
          </div>
        </div>
        <div class="card-footer" style="display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Save Configuration</button>
        </div>
      </form>
    </div>

    {{-- Dashboard Settings --}}
    <div class="card">
      <div class="card-header">
        <i class="ri-layout-3-line" style="color:var(--primary)"></i>
        <span class="card-title">Dashboard Preferences</span>
      </div>
      <form action="{{ route('dashboard.settings.dashboard') }}" method="POST">
        @csrf
        <div class="card-body" style="display:flex;flex-direction:column;gap:14px">
          <div style="display:flex;align-items:center;justify-content:space-between">
            <div>
              <div style="font-size:0.8125rem;font-weight:500;color:var(--text-1)">Auto-refresh dashboard</div>
              <div style="font-size:0.75rem;color:var(--text-3)">Automatically refresh device data every 15 seconds</div>
            </div>
            <label class="toggle-wrap">
              <input type="checkbox" class="toggle-input" name="auto_refresh"
                {{ ($settings['auto_refresh'] ?? true) ? 'checked' : '' }}>
              <div class="toggle-track"></div>
            </label>
          </div>
          <div style="height:1px;background:var(--border-3)"></div>
          <div style="display:flex;align-items:center;justify-content:space-between">
            <div>
              <div style="font-size:0.8125rem;font-weight:500;color:var(--text-1)">Show offline devices</div>
              <div style="font-size:0.75rem;color:var(--text-3)">Display offline devices in the device list</div>
            </div>
            <label class="toggle-wrap">
              <input type="checkbox" class="toggle-input" name="show_offline"
                {{ ($settings['show_offline'] ?? true) ? 'checked' : '' }}>
              <div class="toggle-track"></div>
            </label>
          </div>
          <div style="height:1px;background:var(--border-3)"></div>
          <div class="form-group">
            <label class="form-label">Default chart range</label>
            <select class="form-select" name="default_chart_range">
              @foreach(['1h'=>'Last 1 hour','6h'=>'Last 6 hours','24h'=>'Last 24 hours','7d'=>'Last 7 days'] as $val=>$label)
                <option value="{{ $val }}" {{ ($settings['default_chart_range'] ?? '6h') === $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="card-footer" style="display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Save Preferences</button>
        </div>
      </form>
    </div>

  </div>

  {{-- RIGHT COLUMN --}}
  <div style="display:flex;flex-direction:column;gap:16px">

    {{-- API Key Management --}}
    <div class="card">
      <div class="card-header">
        <i class="ri-key-2-line" style="color:var(--primary)"></i>
        <span class="card-title">API Keys</span>
        <button class="btn btn-primary btn-sm" data-modal-open="modal-add-key">
          <i class="ri-add-line"></i> New Key
        </button>
      </div>
      <div style="display:flex;flex-direction:column">
        @forelse($apiClients as $client)
        <div style="padding:14px 18px;border-bottom:1px solid var(--border-3)">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
            <div style="flex:1;min-width:0">
              <div style="font-size:0.8125rem;font-weight:500;color:var(--text-1)">{{ $client->name }}</div>
              <div style="display:flex;align-items:center;gap:6px;margin-top:5px">
                <code id="key-{{ $client->id }}" style="font-size:0.6875rem;background:var(--bg-page);padding:3px 8px;border-radius:var(--r-md);border:1px solid var(--border-2);color:var(--text-2);letter-spacing:0.03em;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                  {{ Str::mask($client->api_key, '*', 8) }}
                </code>
                <button class="btn btn-ghost btn-sm btn-icon-only" data-tip="Copy key"
                  onclick="copyKey('{{ $client->api_key }}')">
                  <i class="ri-file-copy-line"></i>
                </button>
              </div>
              <div style="margin-top:6px;display:flex;align-items:center;gap:8px">
                <span class="badge {{ $client->is_active ? 'badge-success' : 'badge-gray' }}">
                  {{ $client->is_active ? 'Active' : 'Inactive' }}
                </span>
                <span style="font-size:0.6875rem;color:var(--text-4)">
                  Created {{ $client->created_at->format('d M Y') }}
                </span>
                <span style="font-size:0.6875rem;color:var(--text-4)">
                  {{ $client->devices->count() }} device(s)
                </span>
              </div>
            </div>
            <div class="dropdown">
              <button class="btn btn-ghost btn-sm btn-icon-only" data-dropdown="key-menu-{{ $client->id }}">
                <i class="ri-more-2-line"></i>
              </button>
              <div class="dropdown-menu" id="key-menu-{{ $client->id }}">
                <form action="{{ route('dashboard.settings.key.toggle', $client->id) }}" method="POST">
                  @csrf @method('PATCH')
                  <button type="submit" class="dropdown-item" style="width:100%;background:none;border:none;text-align:left;cursor:pointer">
                    <i class="ri-{{ $client->is_active ? 'forbid' : 'check' }}-line"></i>
                    {{ $client->is_active ? 'Deactivate' : 'Activate' }}
                  </button>
                </form>
                <form action="{{ route('dashboard.settings.key.regenerate', $client->id) }}" method="POST">
                  @csrf @method('PATCH')
                  <button type="submit" class="dropdown-item" style="width:100%;background:none;border:none;text-align:left;cursor:pointer">
                    <i class="ri-refresh-line"></i> Regenerate
                  </button>
                </form>
                <div class="dropdown-divider"></div>
                <form action="{{ route('dashboard.settings.key.delete', $client->id) }}" method="POST"
                  onsubmit="return confirm('Delete this API key?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="dropdown-item" style="width:100%;background:none;border:none;text-align:left;cursor:pointer;color:var(--danger)">
                    <i class="ri-delete-bin-line"></i> Delete
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
        @empty
        <div class="empty-state">
          <i class="ri-key-2-line"></i>
          <span class="empty-title">No API keys yet</span>
        </div>
        @endforelse
      </div>
    </div>

    {{-- System Info --}}
    <div class="card">
      <div class="card-header">
        <i class="ri-information-line" style="color:var(--primary)"></i>
        <span class="card-title">System Info</span>
      </div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
        @foreach([
          ['Laravel Version', app()->version()],
          ['PHP Version',     phpversion()],
          ['Environment',     app()->environment()],
          ['Database',        'MySQL'],
          ['Total Devices',   $sysInfo['total_devices']],
          ['Total Records',   $sysInfo['total_records']],
          ['Total Commands',  $sysInfo['total_commands']],
          ['Total Logs',      $sysInfo['total_logs']],
        ] as [$label, $val])
        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border-3)">
          <span style="font-size:0.8125rem;color:var(--text-3)">{{ $label }}</span>
          <span style="font-size:0.8125rem;font-weight:500;color:var(--text-1)">{{ $val }}</span>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Danger zone --}}
    <div class="card" style="border-color:var(--danger-lt)">
      <div class="card-header" style="background:var(--danger-bg)">
        <i class="ri-alert-line" style="color:var(--danger)"></i>
        <span class="card-title" style="color:var(--danger)">Danger Zone</span>
      </div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
          <div>
            <div style="font-size:0.8125rem;font-weight:500;color:var(--text-1)">Clear sensor data</div>
            <div style="font-size:0.75rem;color:var(--text-3)">Delete all sensor readings from database</div>
          </div>
          <button class="btn btn-danger btn-sm" data-modal-open="modal-clear-sensor">
            <i class="ri-delete-bin-line"></i> Clear
          </button>
        </div>
        <div style="height:1px;background:var(--border-2)"></div>
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
          <div>
            <div style="font-size:0.8125rem;font-weight:500;color:var(--text-1)">Clear activity log</div>
            <div style="font-size:0.75rem;color:var(--text-3)">Delete all activity log entries</div>
          </div>
          <button class="btn btn-danger btn-sm" data-modal-open="modal-clear-log">
            <i class="ri-delete-bin-line"></i> Clear
          </button>
        </div>
      </div>
    </div>

  </div>
</div>

@endsection

@push('modals')
{{-- Add API Key --}}
<div class="modal-overlay" id="modal-add-key">
  <div class="modal">
    <div class="modal-header">
      <i class="ri-key-2-line" style="color:var(--primary)"></i>
      <span class="modal-title">Create API Key</span>
      <button class="modal-close" data-modal-close="modal-add-key"><i class="ri-close-line"></i></button>
    </div>
    <form action="{{ route('dashboard.settings.key.store') }}" method="POST">
      @csrf
      <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
        <div class="form-group">
          <label class="form-label">Client Name</label>
          <input type="text" class="form-input" name="name" placeholder="e.g. Python Device Client" required>
        </div>
        <div style="padding:10px 12px;background:var(--info-bg);border-radius:var(--r-md);font-size:0.75rem;color:var(--info);display:flex;gap:8px">
          <i class="ri-information-line" style="flex-shrink:0;margin-top:1px"></i>
          API key will be generated automatically and shown once. Copy it immediately.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close="modal-add-key">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="ri-add-line"></i> Generate Key</button>
      </div>
    </form>
  </div>
</div>

{{-- Clear sensor data confirm --}}
<div class="modal-overlay" id="modal-clear-sensor">
  <div class="modal">
    <div class="modal-header">
      <i class="ri-alert-line" style="color:var(--danger)"></i>
      <span class="modal-title">Clear Sensor Data</span>
      <button class="modal-close" data-modal-close="modal-clear-sensor"><i class="ri-close-line"></i></button>
    </div>
    <div class="modal-body">
      <p style="font-size:0.875rem;color:var(--text-2)">This will permanently delete <strong>all sensor readings</strong>. This action cannot be undone.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-modal-close="modal-clear-sensor">Cancel</button>
      <form action="{{ route('dashboard.settings.clear.sensor') }}" method="POST">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger"><i class="ri-delete-bin-line"></i> Yes, Clear</button>
      </form>
    </div>
  </div>
</div>

{{-- Clear log confirm --}}
<div class="modal-overlay" id="modal-clear-log">
  <div class="modal">
    <div class="modal-header">
      <i class="ri-alert-line" style="color:var(--danger)"></i>
      <span class="modal-title">Clear Activity Log</span>
      <button class="modal-close" data-modal-close="modal-clear-log"><i class="ri-close-line"></i></button>
    </div>
    <div class="modal-body">
      <p style="font-size:0.875rem;color:var(--text-2)">This will permanently delete <strong>all activity log entries</strong>. This action cannot be undone.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-modal-close="modal-clear-log">Cancel</button>
      <form action="{{ route('dashboard.settings.clear.log') }}" method="POST">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger"><i class="ri-delete-bin-line"></i> Yes, Clear</button>
      </form>
    </div>
  </div>
</div>
@endpush

@push('scripts')
<script>
function copyKey(key) {
  navigator.clipboard.writeText(key).then(() => Toast.show('success', 'Copied', 'API key copied to clipboard.'));
}

const rangeMoisture = document.getElementById('range-moisture');
const rangeStop     = document.getElementById('range-stop');
rangeMoisture.addEventListener('input', function() {
  document.getElementById('preview-trigger').textContent = this.value + '%';
  document.getElementById('preview-bar').style.width = this.value + '%';
});
rangeStop.addEventListener('input', function() {
  document.getElementById('preview-stop').textContent = this.value + '%';
});
</script>
@endpush