@extends('layouts.app')
@section('title', $title ?? 'Module')
@section('page-title', $title ?? 'Module')
@section('content')
<div class="text-center py-5">
    <div style="width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,#ede9fe,#ddd6fe);display:inline-flex;align-items:center;justify-content:center;margin-bottom:20px;">
        <i class="{{ $icon ?? 'bi bi-gear' }}" style="font-size:36px;color:#6366f1;"></i>
    </div>
    <h2 style="font-size:24px;font-weight:700;color:#0f172a;margin-bottom:8px;">{{ $title ?? 'Module' }}</h2>
    <p style="color:#64748b;font-size:15px;max-width:400px;margin:0 auto 24px;">
        This module is under active development and will be available soon.
    </p>
    <div style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:#f1f5f9;border-radius:20px;font-size:13px;color:#64748b;">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        Building...
    </div>
</div>
@endsection
