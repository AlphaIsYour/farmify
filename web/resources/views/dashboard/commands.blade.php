@extends('layouts.app')
@section('title', 'Commands')
@section('breadcrumb', 'Commands')

@section('content')

<div class="page-header flex-between">
  <div>
    <h1 class="page-title">Command History</h1>
    <p class="page-subtitle">All commands sent to devices</p>
  </div>
  <button class="btn btn-primary" data-modal-open="modal-send-cmd">
    <i class="ri-send-plane-line"></i> Send Command
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
    <select class="form-select" style="width:140px" id="f-type">
      <option value="">All Types</option>
      <option value="start_pump">Start Pump</option>
      <option value="stop_pump">Stop Pump</option>
    </select>
    <select class="form-select" style="width:140px" id="f-status">
      <option value="">All Status</option>
      <option value="pending">Pending</option>
      <option value="processing">Processing</option>
      <option value="done">Done</option>
      <option value="failed">Failed</option>
    </select>
    <select class="form-select" style="width:130px" id="f-source">
      <option value="">All Sources</option>
      <option value="manual">Manual</option>
      <option value="auto">Auto</option>
    </select>
    <button class="btn btn-ghost btn-sm" id="apply-filter"><i class="ri-filter-line"></i> Apply</button>
    <button class="btn btn-ghost btn-sm" onclick="window.location='{{ route('dashboard.commands') }}'"><i class="ri-close-line"></i> Clear</button>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px" class="row-gap">
  @foreach([
    ['Done',       $cmdStats['done'],       'badge-success', 'ri-check-double-line'],
    ['Pending',    $cmdStats['pending'],    'badge-warning', 'ri-time-line'],
    ['Failed',     $cmdStats['failed'],     'badge-danger',  'ri-error-warning-line'],
    ['Auto',       $cmdStats['auto'],       'badge-accent',  'ri-robot-2-line'],
  ] as [$label,$val,$badge,$icon])
  <div style="background:var(--bg-surface);border-radius:var(--r-lg);padding:14px 18px;border:1px solid var(--border-2);box-shadow:0 1px 0 rgba(255,255,255,0.8) inset,0 -1px 0 rgba(0,0,0,0.04) inset,0 2px 6px rgba(0,0,0,0.07),0 8px 24px rgba(0,0,0,0.05);display:flex;align-items:center;gap:12px">
    <span class="badge {{ $badge }}" style="width:36px;height:36px;border-radius:var(--r-md);font-size:16px;flex-shrink:0;padding:0;justify-content:center;box-shadow:0 1px 3px rgba(0,0,0,0.08)">
      <i class="{{ $icon }}"></i>
    </span>
    <div>
      <div style="font-family:var(--font-h);font-size:1.25rem;font-weight:600;color:var(--text-1)">{{ $val }}</div>
      <div style="font-size:0.75rem;color:var(--text-3)">{{ $label }}</div>
    </div>
  </div>
  @endforeach
</div>

<div class="card">
  <div class="card-header">
    <i class="ri-remote-control-line" style="color:var(--primary)"></i>
    <span class="card-title">Commands</span>
    <span class="badge badge-gray">{{ $commands->total() }}</span>
    <div class="flex gap-2" style="margin-left:auto">
      <div style="display:flex;align-items:center;gap:6px;font-size:0.75rem;color:var(--text-3)">
        <span class="spinner spinner-sm" id="table-spinner" style="display:none"></span>
      </div>
    </div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Device</th>
          <th>Type</th>
          <th>Source</th>
          <th>Status</th>
          <th>Sent At</th>
          <th>Executed At</th>
        </tr>
      </thead>
      <tbody>
        @forelse($commands as $cmd)
        <tr>
          <td class="td-muted td-mono">#{{ $cmd->id }}</td>
          <td>
            <div style="font-weight:500;font-size:0.8125rem">{{ $cmd->device->device_code }}</div>
            <div class="td-muted">{{ $cmd->device->zone_name }}</div>
          </td>
          <td>
            <span class="badge {{ $cmd->command_type === 'start_pump' ? 'badge-success' : 'badge-gray' }}">
              <i class="ri-{{ $cmd->command_type === 'start_pump' ? 'play' : 'stop' }}-line"></i>
              {{ $cmd->command_type === 'start_pump' ? 'Start Pump' : 'Stop Pump' }}
            </span>
          </td>
          <td>
            <span class="badge {{ $cmd->source === 'auto' ? 'badge-accent' : 'badge-info' }}">
              <i class="ri-{{ $cmd->source === 'auto' ? 'robot' : 'user' }}-line"></i>
              {{ ucfirst($cmd->source) }}
            </span>
          </td>
          <td>
            @php
              $statusMap = ['done'=>'badge-success','failed'=>'badge-danger','pending'=>'badge-warning','processing'=>'badge-info'];
              $iconMap   = ['done'=>'ri-check-double-line','failed'=>'ri-error-warning-line','pending'=>'ri-time-line','processing'=>'ri-loader-4-line'];
            @endphp
            <span class="badge {{ $statusMap[$cmd->status] ?? 'badge-gray' }}">
              <i class="{{ $iconMap[$cmd->status] ?? 'ri-question-line' }}"></i>
              {{ ucfirst($cmd->status) }}
            </span>
          </td>
          <td class="td-muted">{{ $cmd->created_at->format('d M, H:i') }}</td>
          <td class="td-muted">{{ $cmd->executed_at ? $cmd->executed_at->format('d M, H:i') : '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="7">
          <div class="empty-state">
            <i class="ri-remote-control-line"></i>
            <span class="empty-title">No commands found</span>
          </div>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($commands->hasPages())
  <div class="card-footer flex-between">
    <span class="text-xs text-muted">{{ $commands->firstItem() }}–{{ $commands->lastItem() }} of {{ $commands->total() }}</span>
    {{ $commands->appends(request()->query())->links('dashboard.partials.pagination') }}
  </div>
  @endif
</div>

@endsection

@push('modals')
<div class="modal-overlay" id="modal-send-cmd">
  <div class="modal">
    <div class="modal-header">
      <i class="ri-send-plane-line" style="color:var(--primary)"></i>
      <span class="modal-title">Send Command</span>
      <button class="modal-close" data-modal-close="modal-send-cmd"><i class="ri-close-line"></i></button>
    </div>
    <div class="modal-body">
      <div style="display:flex;flex-direction:column;gap:14px">
        <div class="form-group">
          <label class="form-label">Target Device</label>
          <select class="form-select" id="cmd-device-id">
            @foreach($devices as $d)
              <option value="{{ $d->id }}">{{ $d->zone_name }} ({{ $d->device_code }})</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Command Type</label>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            @foreach([['start_pump','ri-play-line','Start Pump','success'],['stop_pump','ri-stop-line','Stop Pump','danger']] as [$val,$icon,$label,$type])
            <label style="cursor:pointer">
              <input type="radio" name="cmd-type" value="{{ $val }}" style="display:none" {{ $val === 'start_pump' ? 'checked' : '' }}>
              <div class="cmd-option" data-val="{{ $val }}" style="padding:12px;border:1.5px solid var(--border-1);border-radius:var(--r-lg);text-align:center;transition:border-color var(--t-fast),background var(--t-fast)">
                <i class="{{ $icon }}" style="font-size:20px;color:var(--{{ $type }});margin-bottom:4px;display:block"></i>
                <div style="font-size:0.8125rem;font-weight:500;color:var(--text-1)">{{ $label }}</div>
              </div>
            </label>
            @endforeach
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-modal-close="modal-send-cmd">Cancel</button>
      <button class="btn btn-primary" id="confirm-send-cmd">
        <i class="ri-send-plane-line"></i> Send
      </button>
    </div>
  </div>
</div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  @if(session('success'))
    Toast.show('success', 'Success', '{{ session('success') }}');
  @endif

  document.querySelectorAll('.cmd-option').forEach(opt => {
    opt.addEventListener('click', () => {
      document.querySelectorAll('.cmd-option').forEach(o => {
        o.style.borderColor = 'var(--border-1)'; o.style.background = '';
      });
      opt.style.borderColor = 'var(--primary)';
      opt.style.background  = 'var(--primary-pale)';
      opt.closest('label').querySelector('input').checked = true;
    });
    if (opt.closest('label').querySelector('input').checked) {
      opt.style.borderColor = 'var(--primary)';
      opt.style.background  = 'var(--primary-pale)';
    }
  });

  document.getElementById('confirm-send-cmd').addEventListener('click', async () => {
    const deviceId = document.getElementById('cmd-device-id').value;
    const type     = document.querySelector('input[name="cmd-type"]:checked')?.value;
    if (!type) return;
    await sendCommand(parseInt(deviceId), type);
    Modal.close('modal-send-cmd');
  });

  document.getElementById('apply-filter').addEventListener('click', () => {
    const params = new URLSearchParams();
    const d = document.getElementById('f-device').value;
    const t = document.getElementById('f-type').value;
    const s = document.getElementById('f-status').value;
    const src = document.getElementById('f-source').value;
    if (d) params.set('device_id', d);
    if (t) params.set('type', t);
    if (s) params.set('status', s);
    if (src) params.set('source', src);
    window.location = '{{ route("dashboard.commands") }}?' + params.toString();
  });
});
</script>
@endpush