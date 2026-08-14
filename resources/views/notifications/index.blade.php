@extends('layouts.app')

@section('title', 'Notification Center - VMRFDU')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 p-4 bg-white rounded-4 shadow-sm border">
    <div>
        <span class="badge bg-primary px-3 py-1 mb-2 rounded-pill shadow-sm"><i class="bi bi-bell-fill me-1"></i> System Alerts</span>
        <h3 class="fw-bold mb-1 text-dark"><i class="bi bi-bell-fill text-primary me-2"></i>Institutional Notification Center</h3>
        <p class="text-muted mb-0 small">System alerts, submission status updates, and authority verification reminders</p>
    </div>
    <form action="{{ route('notifications.readAll') }}" method="POST" class="m-0">
        @csrf
        <button type="submit" class="btn btn-light border px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-check-all text-primary"></i> Mark All as Read
        </button>
    </form>
</div>

<div class="portal-card p-4 bg-white border shadow-sm">
    <div class="d-flex flex-column gap-3">
        @forelse($notifications as $notif)
            @php
                $typeColor = $notif->type === 'success' ? 'success' : ($notif->type === 'warning' ? 'warning' : ($notif->type === 'alert' ? 'danger' : 'primary'));
                $typeIcon = $notif->type === 'success' ? 'check-circle-fill' : ($notif->type === 'warning' ? 'exclamation-triangle-fill' : ($notif->type === 'alert' ? 'x-circle-fill' : 'info-circle-fill'));
            @endphp
            <div class="d-flex align-items-start justify-content-between p-4 rounded-4 border shadow-sm {{ !$notif->is_read ? 'bg-primary bg-opacity-5 border-primary border-opacity-25' : 'bg-light' }}">
                <div class="d-flex gap-3 flex-grow-1">
                    <div class="text-{{ $typeColor }} flex-shrink-0 pt-1">
                        <i class="bi bi-{{ $typeIcon }} fs-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark d-flex align-items-center gap-2 mb-1 flex-wrap">
                            {{ $notif->title }}
                            @if(!$notif->is_read)
                                <span class="badge bg-primary text-white rounded-pill" style="font-size: 0.65rem;">NEW</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-2">{{ $notif->message }}</p>
                        <span class="text-xs text-secondary d-flex align-items-center gap-1">
                            <i class="bi bi-clock"></i>
                            {{ $notif->created_at->format('d M Y, h:i A') }} &bull; {{ $notif->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
                @if($notif->action_url)
                    <a href="{{ $notif->action_url }}" class="btn btn-sm btn-light border px-3 rounded-pill shadow-sm ms-3 flex-shrink-0">
                        <i class="bi bi-arrow-right text-primary"></i> View
                    </a>
                @endif
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <div class="bg-light rounded-circle mx-auto p-4 d-inline-flex mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-bell-slash fs-1 opacity-50"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No Notifications Yet</h5>
                <p class="small">System alerts and submission status updates will appear here.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="mt-4 d-flex justify-content-center border-top pt-4">
        {{ $notifications->links() }}
    </div>
    @endif
</div>
@endsection
