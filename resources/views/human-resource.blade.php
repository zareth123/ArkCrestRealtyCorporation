@extends('layouts.dashboard')
@section('title', 'Human Resource')
@section('content')

<div class="welcome-banner" style="background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:20px;padding:36px 40px;margin-bottom:28px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25);">
    <div style="position:relative;z-index:2;">
        <div style="font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px;">Human Resource</div>
        <h1 style="font-size:28px;font-weight:700;color:white;margin:0 0 8px;">Happy ArkCrest Morning, {{ auth()->user()->preferred_address ? auth()->user()->preferred_address.' '.auth()->user()->name : auth()->user()->name }}! 👥</h1>
        <p style="font-size:14px;color:rgba(255,255,255,.75);margin:0;display:flex;align-items:center;gap:8px;">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            HR Overview — {{ date('F Y') }}
        </p>
    </div>
    <div style="position:absolute;top:0;right:0;width:300px;height:100%;pointer-events:none;">
        <div style="position:absolute;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.05);top:-60px;right:-40px;"></div>
        <div style="position:absolute;width:150px;height:150px;border-radius:50%;background:rgba(255,255,255,.05);bottom:-30px;right:80px;"></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:28px;">

    {{-- Total Employees --}}
    <div style="background:white;border-radius:12px;padding:28px 24px;display:flex;align-items:center;gap:20px;box-shadow:0 2px 8px rgba(0,0,0,.08);border-left:5px solid #1e4575;">
        <div style="width:52px;height:52px;border-radius:12px;background:linear-gradient(135deg,#1e4575,#2563eb);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Total Employees</div>
            <div style="font-size:32px;font-weight:800;color:#0f172a;line-height:1;">{{ $totalEmployees }}</div>
            <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Active staff members</div>
        </div>
    </div>

    {{-- Total Agents --}}
    <div style="background:white;border-radius:12px;padding:28px 24px;display:flex;align-items:center;gap:20px;box-shadow:0 2px 8px rgba(0,0,0,.08);border-left:5px solid #A37929;">
        <div style="width:52px;height:52px;border-radius:12px;background:linear-gradient(135deg,#A37929,#d4a03a);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Total Agents</div>
            <div style="font-size:32px;font-weight:800;color:#0f172a;line-height:1;">{{ $totalAgents }}</div>
            <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Sales agents on record</div>
        </div>
    </div>

</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px;">
    <a href="{{ route('settings') }}?panel=employee" style="background:white;border-radius:12px;padding:20px 24px;display:flex;align-items:center;gap:16px;box-shadow:0 2px 8px rgba(0,0,0,.08);text-decoration:none;border:2px solid #e2e8f0;transition:border-color .2s;">
        <div style="width:44px;height:44px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="22" height="22" fill="none" stroke="#1e4575" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <div style="font-size:14px;font-weight:700;color:#0f172a;">Employee Data</div>
            <div style="font-size:12px;color:#64748b;margin-top:2px;">View and manage employee records</div>
        </div>
        <svg style="margin-left:auto;color:#94a3b8;" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
    <a href="{{ route('settings') }}?panel=personnel" style="background:white;border-radius:12px;padding:20px 24px;display:flex;align-items:center;gap:16px;box-shadow:0 2px 8px rgba(0,0,0,.08);text-decoration:none;border:2px solid #e2e8f0;transition:border-color .2s;">
        <div style="width:44px;height:44px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="22" height="22" fill="none" stroke="#1e4575" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        </div>
        <div>
            <div style="font-size:14px;font-weight:700;color:#0f172a;">ARC Contact List</div>
            <div style="font-size:12px;color:#64748b;margin-top:2px;">ArkCrest personnel contact directory</div>
        </div>
        <svg style="margin-left:auto;color:#94a3b8;" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
</div>

@endsection
