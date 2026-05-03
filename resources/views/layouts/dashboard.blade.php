<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/ArkCrest_Logo.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ARCKREST REALTY CORPORATION') }} - @yield('title', 'Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ time() }}{{ rand(1000, 9999) }}">
    <link rel="stylesheet" href="{{ asset('css/optimized-global.css') }}?v={{ time() }}">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header - Full Width -->
        <header class="header">
            <div class="header-content">
                <!-- Logo and Company Name -->
                <div class="header-left">
                    <div class="logo">
                        <img src="{{ asset('images/ArkCrest_Logo.png') }}" alt="ARCKREST Logo" class="logo-img">
                    </div>
                    <h1 class="company-name">ARCKREST REALTY CORPORATION</h1>
                </div>

                <!-- Right Side: Search and Notification -->
                <div class="header-right">
                    <!-- Search -->
                    <div class="search-container">
                        <button id="searchToggle" class="hdr-btn" title="Search">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                        <div id="searchBar" class="search-bar">
                            <input type="text" id="globalSearchInput" placeholder="Search pages, departments..." class="search-input" autocomplete="off">
                            <div id="searchResults" class="search-results"></div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="notes-container" style="position:relative;">
                        <button id="notesToggle" class="hdr-btn" title="My Notes">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            @if(isset($userNotes) && $userNotes->count() > 0)
                            <span class="hdr-badge hdr-badge-gold">{{ $userNotes->count() > 9 ? '9+' : $userNotes->count() }}</span>
                            @endif
                        </button>
                        <div id="notesPanel" style="display:none;position:absolute;top:calc(100% + 10px);right:0;width:320px;background:white;border-radius:10px;box-shadow:0 4px 20px rgba(30,69,117,.15);border:1.5px solid #e2e8f0;z-index:9999;overflow:hidden;">
                            <div style="background:linear-gradient(135deg,#1e4575,#2563eb);padding:12px 16px;display:flex;align-items:center;justify-content:space-between;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <svg fill="none" stroke="white" viewBox="0 0 24 24" style="width:15px;height:15px;flex-shrink:0;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <h3 style="color:white;font-size:13px;font-weight:700;margin:0;">My Notes</h3>
                                </div>
                                <span id="notesCount" style="color:rgba(255,255,255,.8);font-size:10px;background:rgba(255,255,255,.2);padding:2px 8px;border-radius:20px;font-weight:600;">{{ isset($userNotes) ? $userNotes->count() : 0 }} note(s)</span>
                            </div>
                            {{-- Add Note Form --}}
                            <div style="padding:12px 14px;border-bottom:1px solid #f1f5f9;background:#fafbfc;">
                                <form method="POST" action="{{ route('notes.store') }}">
                                    @csrf
                                    <input type="text" name="title" placeholder="Note title..." required style="width:100%;padding:7px 10px;border:1.5px solid #e2e8f0;border-radius:7px;font-size:12px;margin-bottom:6px;box-sizing:border-box;outline:none;font-family:inherit;">
                                    <textarea name="body" placeholder="Details (optional)..." rows="2" style="width:100%;padding:7px 10px;border:1.5px solid #e2e8f0;border-radius:7px;font-size:12px;resize:none;margin-bottom:6px;box-sizing:border-box;outline:none;font-family:inherit;"></textarea>
                                    <div style="display:flex;gap:6px;align-items:center;margin-bottom:6px;">
                                        <input type="date" name="note_date" style="flex:1;padding:6px 8px;border:1.5px solid #e2e8f0;border-radius:7px;font-size:11px;outline:none;" value="{{ date('Y-m-d') }}">
                                        <input type="time" name="reminder_time" style="flex:1;padding:6px 8px;border:1.5px solid #e2e8f0;border-radius:7px;font-size:11px;outline:none;">
                                    </div>
                                    <button type="submit" style="width:100%;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;padding:8px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;">+ Add Note</button>
                                </form>
                            </div>
                            {{-- Notes List --}}
                            <div style="max-height:220px;overflow-y:auto;">
                                @if(isset($userNotes) && $userNotes->count() > 0)
                                    @foreach($userNotes as $note)
                                    <div data-note-id="{{ $note->id }}" style="padding:10px 14px;border-bottom:1px solid #f8fafc;display:flex;gap:8px;align-items:flex-start;">
                                        <div style="flex:1;min-width:0;">
                                            <div style="font-size:12px;font-weight:600;color:#0f172a;margin-bottom:1px;">{{ $note->title }}</div>
                                            @if($note->body)
                                            <div style="font-size:11px;color:#64748b;line-height:1.4;margin-bottom:3px;">{{ Str::limit($note->body, 60) }}</div>
                                            @endif
                                            @if($note->note_date)
                                            <div style="font-size:10px;color:#94a3b8;display:flex;align-items:center;gap:3px;margin-top:2px;">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:10px;height:10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ \Carbon\Carbon::parse($note->note_date)->format('M d, Y') }}
                                                @if($note->reminder_time)
                                                &bull; {{ \Carbon\Carbon::parse($note->reminder_time)->format('g:i A') }}
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('notes.destroy', $note->id) }}" style="flex-shrink:0;">
                                            @csrf @method('DELETE')
                                            <button type="submit" style="background:none;border:none;color:#cbd5e1;cursor:pointer;padding:2px;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#cbd5e1'" title="Delete">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:13px;height:13px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                    @endforeach
                                @else
                                <div style="padding:24px;text-align:center;color:#94a3b8;font-size:12px;">
                                    <svg fill="none" stroke="#cbd5e1" viewBox="0 0 24 24" style="width:28px;height:28px;margin:0 auto 6px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    No notes yet. Add one above.
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- Notification -->
                    <div class="notification-container">
                        <button id="notificationToggle" class="hdr-btn" title="Notifications">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            @if(isset($unreadNotifCount) && $unreadNotifCount > 0)
                            <span class="hdr-badge">{{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}</span>
                            @endif
                        </button>
                        <div id="notificationPanel" class="notification-panel">
                            <div class="notification-header">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;color:#1e4575;flex-shrink:0;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    <h3>Notifications</h3>
                                </div>
                                <span class="notification-count">{{ isset($unreadNotifCount) && $unreadNotifCount > 0 ? $unreadNotifCount.' unread' : 'All read' }}</span>
                            </div>
                            <div class="notification-list">
                                @if(isset($sysNotifs) && $sysNotifs->count() > 0)
                                    @foreach($sysNotifs as $notif)
                                    <div class="notification-item {{ $notif->is_read ? '' : 'unread' }}"
                                        style="cursor:{{ in_array($notif->type, ['note_reminder','user_pending','permission_request','commission_reminder','downpayment_reminder','tripping_reminder']) ? 'pointer' : 'default' }};{{ $notif->is_read && !in_array($notif->type, ['user_pending','permission_request','note_reminder','commission_reminder','downpayment_reminder','tripping_reminder']) ? 'opacity:0.5;pointer-events:none;' : '' }}"
                                        @if($notif->type === 'note_reminder')
                                        onclick="event.stopPropagation();openNoteModal({{ $notif->note_id ?? 0 }}, '{{ addslashes($notif->title) }}', '{{ addslashes($notif->message) }}', this, {{ $notif->id }})"
                                        @elseif($notif->type === 'commission_reminder')
                                        onclick="event.stopPropagation();window.location='{{ route('commission-monitoring') }}'"
                                        @elseif($notif->type === 'downpayment_reminder')
                                        onclick="event.stopPropagation();window.location='{{ route('commission-monitoring') }}'"
                                        @elseif($notif->type === 'tripping_reminder')
                                        onclick="event.stopPropagation();window.location='{{ route('site-visit-database') }}'"
                                        @elseif($notif->type === 'user_pending')
                                        onclick="event.stopPropagation();window.location='{{ route('settings') }}?panel=users'"
                                        @elseif($notif->type === 'permission_request' && auth()->user()->isAdmin())
                                        onclick="event.stopPropagation();window.location='{{ route('settings') }}?panel=permission-requests'"
                                        @elseif(in_array($notif->type, ['permission_approved','permission_rejected','permission_sent']))
                                        onclick="event.stopPropagation();handlePermissionNotifClick({{ $notif->id }}, '{{ $notif->note_id }}')"
                                        @endif>
                                        <div class="notification-icon {{ in_array($notif->type, ['permission_approved']) ? 'green' : (in_array($notif->type, ['permission_rejected']) ? 'red' : 'blue') }}"
                                             style="{{ $notif->type === 'permission_approved' ? 'background:#dcfce7;' : ($notif->type === 'permission_rejected' ? 'background:#fee2e2;' : '') }}">
                                            @if($notif->type === 'note_reminder')
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            @elseif($notif->type === 'user_pending')
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            @elseif(in_array($notif->type, ['permission_request','permission_sent']))
                                            <svg fill="none" stroke="{{ $notif->type === 'permission_request' ? '#1d4ed8' : '#1e4575' }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                            @elseif($notif->type === 'permission_approved')
                                            <svg fill="none" stroke="#16a34a" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @elseif($notif->type === 'permission_rejected')
                                            <svg fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @else
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </div>
                                        <div class="notification-content">
                                            <p class="notification-title">{{ $notif->title }}</p>
                                            <p class="notification-text">{{ $notif->message }}</p>
                                            <p class="notification-time">{{ $notif->notified_at->diffForHumans() }}</p>
                                        </div>
                                        @if(!$notif->is_read)<span class="notif-dot"></span>@endif
                                    </div>
                                    @endforeach
                                @else
                                    <div style="padding:28px;text-align:center;color:#9ca3af;font-size:13px;">No notifications yet</div>
                                @endif
                            </div>
                            <div class="notification-footer" style="display:flex;gap:8px;justify-content:space-between;align-items:center;">
                                <button type="button" onclick="notifMarkAllRead()" style="background:none;border:none;color:#1e4575;font-size:12px;font-weight:600;cursor:pointer;padding:0;">Mark all read</button>
                                <button type="button" onclick="notifClearAll()" style="background:none;border:none;color:#ef4444;font-size:12px;font-weight:600;cursor:pointer;padding:0;">Clear All</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Wrapper (Sidebar + Main Content) -->
        <div class="content-wrapper">
            <!-- Sidebar -->
            <aside id="sidebar" class="sidebar sidebar-expanded">
            <!-- User Profile Section -->
            <div class="user-profile">
                <div class="profile-content">
                    <div class="profile-avatar">
                        @if(auth()->user()->avatar)
                            <img src="{{ str_starts_with(auth()->user()->avatar, 'avatars/') ? \Storage::disk('public')->url(auth()->user()->avatar) : asset(auth()->user()->avatar) }}" alt="avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                        @else
                            <span>{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="sidebar-text profile-info">
                        <h3 class="profile-name">{{ auth()->user()->name ?? 'User Name' }}</h3>
                        <p class="profile-email">{{ auth()->user()->email ?? '' }}</p>
                        <span class="profile-role-badge">{{ auth()->user() ? ucfirst(auth()->user()->role) : 'Staff' }}</span>
                    </div>
                </div>
                
                <!-- Toggle Button -->
                <button id="sidebarToggle" class="sidebar-toggle">
                    <svg id="toggleIcon" class="toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="nav-menu">
                @php
                $hiddenPages = [];
                if (auth()->check() && !auth()->user()->isAdmin()) {
                    $allHidden = array_values(auth()->user()->hidden_pages ?? []);
                    $hiddenPages = array_filter($allHidden, fn($k) => strpos($k, '.') === false);
                }
                $canSee = fn($key) => !in_array($key, $hiddenPages);
                @endphp
                <ul class="nav-list">
                    <!-- Finance with Dropdown -->
                    @php
                    $financeChildren = array_filter(['departments','summary-report','commission-monitoring'], fn($k) => $canSee($k));
                    @endphp
                    @if(count($financeChildren) > 0)
                    <li class="nav-item-wrapper">
                        <div class="nav-item-container">
                            <a href="{{ route('dashboard') }}" class="nav-item nav-item-with-dropdown" data-page="dashboard" onclick="event.stopPropagation();">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="sidebar-text">Finance</span>
                            </a>
                            <button class="dropdown-toggle-btn" id="financeDropdownToggle" type="button" onclick="toggleFinanceDropdown(event)">
                                <svg class="dropdown-arrow" id="financeArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>
                        <ul class="nav-submenu" id="financeSubmenu">
                            @if($canSee('departments'))
                            <li>
                                <a href="{{ route('departments.admin') }}" class="nav-subitem" data-page="departments">
                                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <span class="sidebar-text">Departments</span>
                                </a>
                            </li>
                            @endif
                            @if($canSee('summary-report'))
                            <li>
                                <a href="{{ route('summary-report') }}" class="nav-subitem" data-page="summary-report">
                                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="sidebar-text">Summary Report</span>
                                </a>
                            </li>
                            @endif
                            <li>
                                <a href="{{ route('arkcrest-sales') }}" class="nav-subitem" data-page="arkcrest-sales">
                                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="sidebar-text">ARC Sales</span>
                                </a>
                            </li>
                            @if($canSee('commission-monitoring'))
                            <li class="nav-item-wrapper">
                                <div class="nav-item-container">
                                    <a href="{{ route('commission-monitoring') }}" class="nav-subitem nav-item-with-dropdown" data-page="commission-monitoring" onclick="event.stopPropagation();">
                                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                        <span class="sidebar-text" style="font-size:11px;">Commission Monitoring</span>
                                    </a>
                                    <button class="dropdown-toggle-btn" type="button" onclick="toggleCommissionDropdown(event)">
                                        <svg class="dropdown-arrow" id="commissionArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>
                                <ul class="nav-submenu" id="commissionSubmenu" style="padding-left:12px;">
                                    <li>
                                        <a href="{{ route('commission-dashboard') }}" class="nav-subitem" data-page="commission-dashboard" style="font-size:11px;">
                                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:13px;height:13px;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            </svg>
                                            <span class="sidebar-text" style="font-size:10px;">Commission Dashboard</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            @endif
                            @if($canSee('calendar'))
                            <li>
                                <a href="{{ route('calendar') }}" class="nav-subitem" data-page="calendar">
                                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="sidebar-text">Calendar</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif {{-- end finance --}}
                    
                    <!-- Sales & Marketing with Dropdown -->
                    @if($canSee('sales-marketing'))
                    <li class="nav-item-wrapper">
                        <div class="nav-item-container">
                            <a href="{{ route('sales-marketing') }}" class="nav-item nav-item-with-dropdown" data-page="sales-marketing" onclick="event.stopPropagation();">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="sidebar-text">Sales & Marketing</span>
                            </a>
                            <button class="dropdown-toggle-btn" id="salesDropdownToggle" type="button" onclick="toggleSalesDropdown(event)">
                                <svg class="dropdown-arrow" id="salesArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>
                        <ul class="nav-submenu" id="salesSubmenu">
                            <li class="nav-item-wrapper">
                                <div class="nav-item-container">
                                    <a href="{{ route('client-database') }}" class="nav-subitem nav-item-with-dropdown" data-page="client-database" onclick="event.stopPropagation();">
                                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span class="sidebar-text">Client Database</span>
                                    </a>
                                    <button class="dropdown-toggle-btn" id="clientDbDropdownToggle" type="button" onclick="toggleClientDbDropdown(event)">
                                        <svg class="dropdown-arrow" id="clientDbArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>
                                <ul class="nav-submenu" id="clientDbSubmenu" style="padding-left:12px;">
                                    <li>
                                        <a href="{{ route('reserved-clients') }}" class="nav-subitem" data-page="cd-clients" style="font-size:11px;">
                                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:13px;height:13px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span class="sidebar-text">List of Clients</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('property-list') }}" class="nav-subitem" data-page="cd-properties" style="font-size:11px;">
                                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:13px;height:13px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                            <span class="sidebar-text">List of Properties</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="{{ route('site-visit-database') }}" class="nav-subitem" data-page="site-visit-database">
                                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="sidebar-text">Site Visit Database</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('sales-calendar') }}" class="nav-subitem" data-page="sm-calendar">
                                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="sidebar-text">Calendar</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                    
                    <!-- Forms -->
                    @if($canSee('forms'))
                    <li class="nav-item-wrapper">
                        <div class="nav-item-container">
                            <a href="{{ route('human-resource') }}" class="nav-item nav-item-with-dropdown" data-page="human-resource" onclick="event.stopPropagation();">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="sidebar-text">Human Resource</span>
                            </a>
                            <button class="dropdown-toggle-btn" type="button" onclick="toggleHRDropdown(event)">
                                <svg class="dropdown-arrow" id="hrArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>
                        <ul class="nav-submenu" id="hrSubmenu">
                            <li>
                                <a href="{{ route('hr.employee-data') }}" class="nav-subitem" data-page="hr-employee-data">
                                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span class="sidebar-text">Employee Data</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('hr.contact-list') }}" class="nav-subitem" data-page="hr-contact-list">
                                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <span class="sidebar-text">ARC Contact List</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="{{ route('tripping') }}" class="nav-item" data-page="forms-site-visit" target="_self">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span class="sidebar-text">Site Visit Form</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('forms') }}" class="nav-item" data-page="forms">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="sidebar-text">Forms</span>
                        </a>
                    </li>
                    @endif
                    
                    <!-- Settings -->
                    <li>
                        <a href="{{ route('settings') }}" class="nav-item" data-page="settings">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="sidebar-text">Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Logout Button -->
            <div class="logout-section">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item logout-btn">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="sidebar-text">Logout</span>
                    </button>
                </form>
            </div>
            </aside>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Page Content -->
                <main class="page-content">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <script>
    // Close on Escape key - go back
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') history.back();
    });

    // Handle permission notification click — go to the page and highlight the row
    function handlePermissionNotifClick(notifId, permId) {
        const panel = document.getElementById('notificationPanel');
        if (panel) panel.classList.remove('show');

        fetch('/api/permission-requests/by-notif/' + notifId)
            .then(r => r.json())
            .then(data => {
                if (data && data.url) {
                    window.location.href = data.url;
                } else {
                    window.location.href = '/dashboard';
                }
            })
            .catch(() => { window.location.href = '/dashboard'; });
    }

    function handleClientDoneNotifClick(notifId, recordId) {
        const panel = document.getElementById('notificationPanel');
        if (panel) panel.classList.remove('show');

        fetch('/notifications/' + notifId + '/read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        }).catch(() => {});

        fetch('/api/client-database/' + recordId + '/prefill')
            .then(r => r.json())
            .then(data => {
                const params = new URLSearchParams({
                    prefill_client:         data.client_name       || '',
                    prefill_project:        data.project_name      || '',
                    prefill_agent:          data.agent_name        || '',
                    prefill_net_tcp:        data.net_tcp           || '',
                    prefill_reservation:    data.reservation_date  || '',
                    prefill_terms:          data.terms_of_payment  || '',
                    prefill_units:          data.number_of_units   || '',
                    prefill_commission_pct: data.commission_percent || '',
                    prefill_date:           data.date_requested    || '',
                    prefill_developer:      data.developer_name    || '',
                    prefill_block_lot:      data.block_lot_number  || '',
                    prefill_price_sqm:      data.price_sqm         || '',
                    prefill_lot_area:       data.lot_area          || '',
                    prefill_discount:       data.discount          || '',
                    prefill_mode_of_payment: data.mode_of_payment  || '',
                });
                window.location.href = '/commission-monitoring?' + params.toString();
            })
            .catch(() => { window.location.href = '/commission-monitoring'; });
    }

    function handleTripDoneNotifClick(notifId, tripId) {
        const panel = document.getElementById('notificationPanel');
        if (panel) panel.classList.remove('show');

        // Mark as read
        fetch('/notifications/' + notifId + '/read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        }).catch(() => {});

        // Fetch trip data then redirect to client-database with prefill params
        fetch('/api/trips/' + tripId + '/prefill')
            .then(r => r.json())
            .then(data => {
                const params = new URLSearchParams({
                    prefill_client:    data.client_name    || '',
                    prefill_email:     data.client_email   || '',
                    prefill_phone:     data.client_phone   || '',
                    prefill_project:   data.project_name   || '',
                    prefill_agent:     data.agent_name     || '',
                    prefill_developer: data.developer_name || '',
                    prefill_trip:      tripId,
                    prefill_date:      data.date_requested || '',
                });
                window.location.href = '/client-database?' + params.toString();
            })
            .catch(() => { window.location.href = '/client-database'; });
    }
    </script>

    <script>
        // Finance Dropdown Toggle - Inline Function
        function toggleFinanceDropdown(event) {
            event.preventDefault();
            event.stopPropagation();
            
            console.log('🔽 DROPDOWN CLICKED!');
            
            const submenu = document.getElementById('financeSubmenu');
            const arrow = document.getElementById('financeArrow');
            
            if (submenu && arrow) {
                const isOpen = submenu.classList.contains('open');
                
                if (isOpen) {
                    submenu.classList.remove('open');
                    arrow.classList.remove('open');
                    localStorage.setItem('financeDropdownOpen', 'false');
                    console.log('✅ CLOSED');
                } else {
                    submenu.classList.add('open');
                    arrow.classList.add('open');
                    localStorage.setItem('financeDropdownOpen', 'true');
                    console.log('✅ OPENED');
                }
            } else {
                console.error('❌ Elements not found!', { submenu, arrow });
            }
        }

        // Client Database sub-dropdown
        function toggleClientDbDropdown(event) {
            event.preventDefault();
            event.stopPropagation();
            const submenu = document.getElementById('clientDbSubmenu');
            const arrow   = document.getElementById('clientDbArrow');
            if (submenu && arrow) {
                const isOpen = submenu.classList.contains('open');
                if (isOpen) {
                    submenu.classList.remove('open');
                    arrow.classList.remove('open');
                } else {
                    submenu.classList.add('open');
                    arrow.classList.add('open');
                }
            }
        }

        function toggleCommissionDropdown(event) {
            event.preventDefault();
            event.stopPropagation();
            const submenu = document.getElementById('commissionSubmenu');
            const arrow   = document.getElementById('commissionArrow');
            if (submenu && arrow) {
                const isOpen = submenu.classList.contains('open');
                if (isOpen) {
                    submenu.classList.remove('open');
                    arrow.classList.remove('open');
                } else {
                    submenu.classList.add('open');
                    arrow.classList.add('open');
                }
            }
        }

        function toggleHRDropdown(event) {
            event.preventDefault();
            event.stopPropagation();
            const submenu = document.getElementById('hrSubmenu');
            const arrow   = document.getElementById('hrArrow');
            if (submenu && arrow) {
                const isOpen = submenu.classList.contains('open');
                if (isOpen) {
                    submenu.classList.remove('open');
                    arrow.classList.remove('open');
                } else {
                    submenu.classList.add('open');
                    arrow.classList.add('open');
                }
            }
        }

        // Sales & Marketing Dropdown Toggle
        function toggleSalesDropdown(event) {
            event.preventDefault();
            event.stopPropagation();
            const submenu = document.getElementById('salesSubmenu');
            const arrow = document.getElementById('salesArrow');
            if (submenu && arrow) {
                const isOpen = submenu.classList.contains('open');
                if (isOpen) {
                    submenu.classList.remove('open');
                    arrow.classList.remove('open');
                    localStorage.setItem('salesDropdownOpen', 'false');
                } else {
                    submenu.classList.add('open');
                    arrow.classList.add('open');
                    localStorage.setItem('salesDropdownOpen', 'true');
                }
            }
        }

        // Restore dropdown state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const submenu = document.getElementById('financeSubmenu');
            const arrow = document.getElementById('financeArrow');
            const currentPage = window.location.pathname;
            
            // Check if we're on a Finance dropdown page
            const financePages = ['/dashboard', '/departments', '/summary-report', '/commission-monitoring'];
            const isFinancePage = financePages.some(page => currentPage.includes(page));
            
            // Check localStorage or if we're on a finance page
            const shouldBeOpen = localStorage.getItem('financeDropdownOpen') === 'true' || isFinancePage;
            
            if (shouldBeOpen && submenu && arrow) {
                submenu.classList.add('open');
                arrow.classList.add('open');
                console.log('✅ Dropdown restored to OPEN state');
            }

            // Restore Sales dropdown
            const salesSubmenu = document.getElementById('salesSubmenu');
            const salesArrow = document.getElementById('salesArrow');
            const salesPages = ['/sales-marketing', '/client-database'];
            const isSalesPage = salesPages.some(page => currentPage.includes(page));
            const salesShouldBeOpen = localStorage.getItem('salesDropdownOpen') === 'true' || isSalesPage;
            if (salesShouldBeOpen && salesSubmenu && salesArrow) {
                salesSubmenu.classList.add('open');
                salesArrow.classList.add('open');
            }
        });
        
        // Notes Toggle
        const notesToggle = document.getElementById('notesToggle');
        const notesPanel = document.getElementById('notesPanel');
        if (notesToggle && notesPanel) {
            notesToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const isVisible = notesPanel.style.display === 'block';
                // Close others first
                if (notificationPanel) notificationPanel.classList.remove('show');
                const sb = document.getElementById('searchBar');
                if (sb) sb.classList.remove('show');
                notesPanel.style.display = isVisible ? 'none' : 'block';
            });
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.notes-container')) {
                    notesPanel.style.display = 'none';
                }
            });
        }

        // Notification Toggle
        const notificationToggle = document.getElementById('notificationToggle');
        const notificationPanel = document.getElementById('notificationPanel');

        notificationToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            // Close others first
            const np = document.getElementById('notesPanel');
            if (np) np.style.display = 'none';
            const sb = document.getElementById('searchBar');
            if (sb) sb.classList.remove('show');
            notificationPanel.classList.toggle('show');
        });

        // Close notification when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.notification-container')) {
                notificationPanel.classList.remove('show');
            }
        });

        // Notification AJAX actions
        function notifMarkAllRead() {
            fetch('{{ route("notifications.markAllRead") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
            }).then(() => {
                // Remove all unread dots
                document.querySelectorAll('.notif-dot').forEach(d => d.remove());
                document.querySelectorAll('.notification-item.unread').forEach(el => el.classList.remove('unread'));
                // Update badge
                const badge = document.querySelector('#notificationToggle .hdr-badge');
                if (badge) badge.remove();
                const countEl = document.querySelector('.notification-count');
                if (countEl) countEl.textContent = 'All read';
            });
        }

        function notifClearAll() {
            showConfirm('This will clear all notifications. This cannot be undone.', function() {
                fetch('{{ route("notifications.clearAll") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
            }).then(() => {
                const list = document.querySelector('.notification-list');
                if (list) list.innerHTML = '<div style="padding:28px;text-align:center;color:#9ca3af;font-size:13px;">No notifications yet</div>';
                const badge = document.querySelector('#notificationToggle .hdr-badge');
                if (badge) badge.remove();
                const countEl = document.querySelector('.notification-count');
                if (countEl) countEl.textContent = 'All read';
            });
            });
        }
    </script>
    <script src="{{ asset('js/sidebar-toggle.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/sidebar-active.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/global-search.js') }}?v={{ time() }}"></script>

    <script>
    // ===== REAL-TIME NOTIFICATION POLLING (every 30s) =====
    let _lastUnread = {{ isset($unreadNotifCount) ? $unreadNotifCount : 0 }};

    function updateNotifBadge(unread, pendingPerms) {
        const notifBtn = document.getElementById('notificationToggle');
        if (!notifBtn) return;

        // Remove all existing badges on the bell
        notifBtn.querySelectorAll('.hdr-badge, #permBadge').forEach(b => b.remove());

        const total = unread + (pendingPerms || 0);
        if (total > 0) {
            const b = document.createElement('span');
            b.className = 'hdr-badge';
            b.textContent = total > 9 ? '9+' : total;
            notifBtn.appendChild(b);
        }

        // Update the count text in notification panel header
        const countEl = document.querySelector('.notification-count');
        if (countEl) {
            countEl.textContent = unread > 0 ? unread + ' unread' : 'All read';
        }
    }

    function renderNotifItem(n) {
        const typeIcons = {
            note_reminder:       '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
            user_pending:        '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
            permission_request:  '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>',
            permission_sent:     '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>',
            permission_approved: '<svg fill="none" stroke="#16a34a" viewBox="0 0 24 24" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            permission_rejected: '<svg fill="none" stroke="#dc2626" viewBox="0 0 24 24" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            trip_done:           '<svg fill="none" stroke="#16a34a" viewBox="0 0 24 24" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            client_done:         '<svg fill="none" stroke="#16a34a" viewBox="0 0 24 24" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        };
        const iconBg = {
            permission_approved: 'background:#dcfce7;',
            permission_rejected: 'background:#fee2e2;',
            note_reminder:       'background:#dbeafe;',
            trip_done:           'background:#dcfce7;',
            client_done:         'background:#dcfce7;',
        };
        const icon = typeIcons[n.type] || typeIcons.permission_sent;
        const bg = iconBg[n.type] || '';
        const isRead = n.is_read;
        const nonClickable = isRead && !['user_pending','permission_request','note_reminder'].includes(n.type);
        const opacity = nonClickable ? 'opacity:0.5;pointer-events:none;' : '';

        let onclick = '';
        if (n.type === 'note_reminder') {
            const safeTitle = (n.title || '').replace(/'/g, "\\'");
            const safeMsg = (n.message || '').replace(/'/g, "\\'");
            onclick = `onclick="event.stopPropagation();openNoteModal(${n.note_id || 0}, '${safeTitle}', '${safeMsg}', this, ${n.id})"`;
        } else if (n.type === 'user_pending') {
            onclick = `onclick="event.stopPropagation();window.location='/settings?panel=users'"`;
        } else if (n.type === 'permission_request') {
            onclick = `onclick="event.stopPropagation();window.location='/settings?panel=permission-requests'"`;
        } else if (['permission_approved','permission_rejected','permission_sent'].includes(n.type)) {
            onclick = `onclick="event.stopPropagation();handlePermissionNotifClick(${n.id}, '${n.note_id || ''}')"`;
        } else if (n.type === 'trip_done' && n.note_id) {
            onclick = `onclick="event.stopPropagation();handleTripDoneNotifClick(${n.id}, ${n.note_id})"`;
        } else if (n.type === 'client_done' && n.note_id) {
            onclick = `onclick="event.stopPropagation();handleClientDoneNotifClick(${n.id}, ${n.note_id})"`;
        }

        return `<div class="notification-item ${isRead ? '' : 'unread'}" style="cursor:${onclick ? 'pointer' : 'default'};${opacity}" ${onclick}>
            <div class="notification-icon blue" style="${bg}"><span style="display:flex;align-items:center;justify-content:center;">${icon}</span></div>
            <div class="notification-content">
                <p class="notification-title">${n.title}</p>
                <p class="notification-text">${n.message}</p>
                <p class="notification-time">${n.notified_at}</p>
            </div>
            ${!isRead ? '<span class="notif-dot"></span>' : ''}
        </div>`;
    }

    function pollNotifications() {
        fetch('/api/notifications/count')
            .then(r => r.json())
            .then(data => {
                const newUnread = data.unread;
                const pendingPerms = data.pending_perms || 0;

                // Bell animation if new notifications arrived
                if (newUnread > _lastUnread) {
                    const btn = document.getElementById('notificationToggle');
                    if (btn) { btn.style.animation = 'none'; btn.offsetHeight; btn.style.animation = 'bellPulse .5s ease'; }

                    // Refresh notification list if panel is open
                    const panel = document.getElementById('notificationPanel');
                    if (panel && panel.classList.contains('show')) {
                        refreshNotifList();
                    }
                }
                _lastUnread = newUnread;
                updateNotifBadge(newUnread, pendingPerms);
            })
            .catch(() => {});
    }

    function refreshNotifList() {
        fetch('/api/notifications/latest')
            .then(r => r.json())
            .then(notifs => {
                const list = document.querySelector('.notification-list');
                if (!list) return;
                if (!notifs.length) {
                    list.innerHTML = '<div style="padding:28px;text-align:center;color:#9ca3af;font-size:13px;">No notifications yet</div>';
                    return;
                }
                list.innerHTML = notifs.map(renderNotifItem).join('');
                // Update count
                const unread = notifs.filter(n => !n.is_read).length;
                const countEl = document.querySelector('.notification-count');
                if (countEl) countEl.textContent = unread > 0 ? unread + ' unread' : 'All read';
            })
            .catch(() => {});
    }

    // Also refresh when notification panel is opened
    document.addEventListener('DOMContentLoaded', function() {
        const notifToggle = document.getElementById('notificationToggle');
        if (notifToggle) {
            notifToggle.addEventListener('click', function() {
                setTimeout(refreshNotifList, 100);
            });
        }
    });

    // Poll every 30 seconds
    setInterval(pollNotifications, 30000);

    // Ping server every 60 seconds to update last_seen_at
    function pingPresence() {
        fetch('/api/ping', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' } }).catch(() => {});
    }
    pingPresence();
    setInterval(pingPresence, 60000);
    // Also poll after 3 seconds on load
    setTimeout(pollNotifications, 3000);
    </script>
    <style>
    /* Hide scrollbars globally but keep scroll functionality */
    * { scrollbar-width: none; }
    *::-webkit-scrollbar { display: none; }

    @keyframes bellPulse {
        0%   { transform: scale(1); }
        30%  { transform: scale(1.2) rotate(-10deg); }
        60%  { transform: scale(1.1) rotate(8deg); }
        100% { transform: scale(1) rotate(0); }
    }
    </style>

    {{-- Permission Request Modal (for staff) --}}
    @if(!auth()->user()->isAdmin())
    <div id="permissionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99990;align-items:center;justify-content:center;" onclick="if(event.target===this)closePermissionModal()">
      <div style="background:white;border-radius:16px;width:480px;max-width:95vw;box-shadow:0 24px 64px rgba(0,0,0,.2);overflow:hidden;animation:panelFadeIn .25s ease;" id="permissionModalBox">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);padding:18px 22px;display:flex;align-items:center;gap:12px;">
          <div style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg fill="none" stroke="white" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
          </div>
          <div style="flex:1;">
            <div style="color:rgba(255,255,255,.7);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;">Permission Required</div>
            <div id="permModalTitle" style="color:white;font-size:15px;font-weight:700;margin-top:1px;">Request to Edit Record</div>
          </div>
          <button onclick="closePermissionModal()" style="background:rgba(255,255,255,.15);border:none;color:white;width:28px;height:28px;border-radius:6px;cursor:pointer;font-size:18px;line-height:1;">&times;</button>
        </div>
        <div style="padding:20px 22px;">
          <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;margin-bottom:16px;border:1px solid #e2e8f0;">
            <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Record</div>
            <div id="permModalRecord" style="font-size:13px;font-weight:600;color:#1e293b;"></div>
          </div>
          <div style="margin-bottom:16px;">
            <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Reason for Request <span style="color:#dc2626;">*</span></label>
            <textarea id="permReason" rows="4" placeholder="Please explain why you need to perform this action..." style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-family:inherit;resize:none;outline:none;box-sizing:border-box;" onfocus="this.style.borderColor='#1e4575'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
            <div id="permReasonError" style="color:#dc2626;font-size:11px;margin-top:4px;display:none;">Please provide a reason (at least 5 characters).</div>
          </div>
          <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button onclick="closePermissionModal()" style="padding:9px 18px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#374151;cursor:pointer;">Cancel</button>
            <button onclick="submitPermissionRequest()" id="permSubmitBtn" style="padding:9px 20px;background:#1e4575;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
              Send Request
            </button>
          </div>
        </div>
      </div>
    </div>
    @endif

    {{-- Admin Permission Review Panel removed - handled in Settings --}}
    @if(auth()->user()->isAdmin())
    <script>
    function loadPendingPermissions() { /* no-op, handled in settings */ }
    function setupAdminPermPanelClose() { /* no-op */ }
    function closeAdminPermPanel() { /* no-op */ }
    function reviewPermission(id, status) { /* no-op */ }
    document.addEventListener('DOMContentLoaded', function() {
        pollNotifications();
    });
    @endif

    <style>
    @keyframes panelFadeIn { from { opacity:0;transform:translateY(8px); } to { opacity:1;transform:translateY(0); } }
    </style>

    <script>
    // ===== PERMISSION REQUEST SYSTEM =====
    let _permAction = '', _permModule = '', _permRecordId = null, _permRecordLabel = '', _permCallback = null;

    function requestPermission(action, module, recordId, recordLabel, callback) {
        _permAction = action;
        _permModule = module;
        _permRecordId = recordId;
        _permRecordLabel = recordLabel;
        _permCallback = callback;

        document.getElementById('permModalTitle').textContent = 'Request to ' + action.charAt(0).toUpperCase() + action.slice(1) + ' Record';
        document.getElementById('permModalRecord').textContent = recordLabel || ('Record #' + recordId);
        document.getElementById('permReason').value = '';
        document.getElementById('permReasonError').style.display = 'none';
        document.getElementById('permissionModal').style.display = 'flex';
        setTimeout(() => document.getElementById('permReason').focus(), 100);
    }

    function closePermissionModal() {
        document.getElementById('permissionModal').style.display = 'none';
        _permCallback = null;
    }

    function submitPermissionRequest() {
        const reason = document.getElementById('permReason').value.trim();
        if (reason.length < 5) {
            document.getElementById('permReasonError').style.display = 'block';
            return;
        }
        document.getElementById('permReasonError').style.display = 'none';

        const btn = document.getElementById('permSubmitBtn');
        btn.disabled = true;
        btn.textContent = 'Sending...';

        fetch('/api/permission-requests', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({
                action: _permAction,
                module: _permModule,
                record_id: _permRecordId,
                record_label: _permRecordLabel,
                reason: reason
            })
        })
        .then(r => r.json())
        .then(data => {
            closePermissionModal();
            btn.disabled = false;
            btn.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Send Request';
            if (typeof showToast === 'function') {
                showToast('Your request has been sent to admin for approval.', 'success', 'Request Sent');
            }
            // Immediately poll so staff sees their own notification right away
            if (typeof pollNotifications === 'function') pollNotifications();
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = 'Send Request';
        });
    }

    @if(auth()->user()->isAdmin())
    function loadPendingPermissions() {
        const panel = document.getElementById('adminPermPanel');
        if (panel) {
            panel.style.pointerEvents = 'all';
        }
        setupAdminPermPanelClose();
        fetch('/api/permission-requests/pending')
            .then(r => r.json())
            .then(data => {
                const list = document.getElementById('adminPermList');
                if (!data.length) {
                    list.innerHTML = '<div style="text-align:center;padding:24px;color:#94a3b8;font-size:13px;">No pending requests.</div>';
                    return;
                }
                list.innerHTML = data.map(p => `
                    <div style="border:1px solid #f1f5f9;border-radius:10px;padding:12px 14px;margin-bottom:8px;">
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;flex-wrap:wrap;">
                            <span style="font-size:12px;font-weight:700;color:#1e293b;">${p.user?.name || 'Staff'}</span>
                            <span style="background:${p.action==='delete'?'#fef2f2':'#eff6ff'};color:${p.action==='delete'?'#dc2626':'#1d4ed8'};padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;">${p.action}</span>
                            <span style="font-size:11px;color:#64748b;">${p.module}</span>
                        </div>
                        <div style="font-size:12px;color:#374151;margin-bottom:4px;"><strong>Record:</strong> ${p.record_label || '#'+p.record_id}</div>
                        <div style="font-size:12px;color:#64748b;margin-bottom:8px;background:#f8fafc;padding:7px 10px;border-radius:6px;border-left:3px solid #1e4575;">${p.reason}</div>
                        <input type="text" id="note-${p.id}" placeholder="Optional note for staff..." style="width:100%;padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;outline:none;box-sizing:border-box;margin-bottom:8px;">
                        <div style="display:flex;gap:8px;">
                            <button onclick="reviewPermission(${p.id},'approved')" style="flex:1;background:#16a34a;color:white;border:none;border-radius:6px;padding:8px;font-size:12px;font-weight:600;cursor:pointer;">✓ Approve</button>
                            <button onclick="reviewPermission(${p.id},'rejected')" style="flex:1;background:#dc2626;color:white;border:none;border-radius:6px;padding:8px;font-size:12px;font-weight:600;cursor:pointer;">✕ Reject</button>
                        </div>
                    </div>
                `).join('');
            });
    }

    function reviewPermission(id, status) {
        const note = document.getElementById('note-' + id)?.value || '';
        fetch('/api/permission-requests/' + id + '/review', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ status, admin_note: note })
        })
        .then(r => r.json())
        .then(() => {
            loadPendingPermissions();
            if (typeof showToast === 'function') showToast('Request ' + status + '.', 'success', 'Done');
        });
    }

    function closeAdminPermPanel() {
        const panel = document.getElementById('adminPermPanel');
        if (panel) {
            panel.style.display = 'none';
            panel.style.pointerEvents = 'none';
        }
    }

    // Close adminPermPanel on next click outside (one-time listener after a small delay)
    function setupAdminPermPanelClose() {
        setTimeout(function() {
            function outsideClick(e) {
                const panel = document.getElementById('adminPermPanel');
                if (!panel || panel.style.display === 'none') {
                    document.removeEventListener('click', outsideClick);
                    return;
                }
                if (!panel.contains(e.target)) {
                    closeAdminPermPanel();
                    document.removeEventListener('click', outsideClick);
                }
            }
            document.addEventListener('click', outsideClick);
        }, 200);
    }

    // Show admin perm panel when clicking notification bell if there are pending requests
    document.addEventListener('DOMContentLoaded', function() {
        // Let pollNotifications handle the badge on load
        pollNotifications();
    });
    @endif
    </script>

    <!-- Toast Container -->
    <div id="toastContainer" style="position:fixed;top:80px;right:24px;z-index:99999;display:flex;flex-direction:column;gap:10px;pointer-events:none;"></div>

    <!-- Global Confirm Dialog -->
    <div id="globalConfirmDialog" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:99998;align-items:center;justify-content:center;">
        <div style="background:white;border-radius:14px;padding:28px 28px 22px;width:380px;max-width:92vw;box-shadow:0 20px 60px rgba(0,0,0,.2);text-align:center;">
            <div style="width:48px;height:48px;background:#fef3c7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                <svg fill="none" stroke="#d97706" viewBox="0 0 24 24" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div id="gc-title" style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:6px;">Are you sure?</div>
            <div id="gc-message" style="font-size:13px;color:#64748b;margin-bottom:20px;line-height:1.5;"></div>
            <div style="display:flex;gap:10px;">
                <button onclick="_gcYes()" style="flex:1;padding:10px;background:linear-gradient(135deg,#dc2626,#ef4444);color:white;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;">Yes, proceed</button>
                <button onclick="_gcNo()" style="flex:1;padding:10px;background:#f1f5f9;color:#374151;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Note Reminder Modal -->
    <div id="noteReminderModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99998;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
        <div style="background:white;border-radius:20px;padding:0;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);overflow:hidden;">
            <div style="background:linear-gradient(135deg,#1e4575,#2563eb);padding:20px 24px;display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg fill="none" stroke="white" viewBox="0 0 24 24" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div style="flex:1;">
                    <div style="color:rgba(255,255,255,.7);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Note Reminder</div>
                    <div id="noteModalTitle" style="color:white;font-size:16px;font-weight:700;margin-top:2px;"></div>
                </div>
                <button onclick="document.getElementById('noteReminderModal').style.display='none'" style="background:rgba(255,255,255,.15);border:none;color:white;width:28px;height:28px;border-radius:6px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">&times;</button>
            </div>
            <div style="padding:24px;">
                <div id="noteModalBody" style="font-size:14px;color:#475569;line-height:1.6;margin-bottom:16px;min-height:40px;"></div>
                <div id="noteModalMeta" style="font-size:12px;color:#94a3b8;margin-bottom:20px;display:flex;gap:12px;flex-wrap:wrap;"></div>
                <div style="display:flex;gap:10px;">
                    <button id="noteModalDoneBtn" onclick="noteModalDone()" style="flex:1;background:linear-gradient(135deg,#059669,#10b981);color:white;border:none;padding:11px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;">Done — Don't remind again</button>
                    <button id="noteModalSnoozeBtn" onclick="noteModalSnooze()" style="flex:1;background:#f1f5f9;color:#374151;border:1.5px solid #e2e8f0;padding:11px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;">Remind me in 30 min</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function updateNotesCount() {
        var remaining = document.querySelectorAll('#notesPanel [data-note-id]').length;
        var el = document.getElementById('notesCount');
        if (el) el.textContent = remaining + ' note(s)';
        // Also update the badge on the notes icon
        var notesBadge = document.querySelector('#notesToggle span');
        if (notesBadge) {
            if (remaining <= 0) notesBadge.remove();
            else notesBadge.textContent = remaining > 9 ? '9+' : remaining;
        }
    }
    function injectPastEvent(data) {
        var container = document.getElementById('pastEventsContainer');
        if (!container || !data || !data.title) return;
        var empty = container.querySelector('.past-events-empty');
        if (empty) empty.remove();
        var html = '<div style="padding:12px 16px;background:#f8fafc;border-radius:10px;border:1px solid #f1f5f9;margin-bottom:8px;display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">'
            + '<div style="flex:1;">'
            + '<div style="font-size:13px;font-weight:600;color:#475569;margin-bottom:2px;">' + data.title + '</div>'
            + (data.body ? '<div style="font-size:12px;color:#94a3b8;">' + data.body + '</div>' : '')
            + '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px;">'
            + (data.note_date ? '<span style="font-size:11px;color:#94a3b8;background:#f1f5f9;padding:2px 8px;border-radius:20px;">' + data.note_date + '</span>' : '')
            + '<span style="font-size:11px;color:#10b981;background:#d1fae5;padding:2px 8px;border-radius:20px;">Done just now</span>'
            + '</div></div></div>';
        container.insertAdjacentHTML('afterbegin', html);
    }
    var _activeNoteId = null;
    var _activeNotifEl = null;
    var _activeNotifId = null;
    function openNoteModal(noteId, title, message, el, notifId) {
        _activeNoteId = noteId || null;
        _activeNotifEl = el || null;
        _activeNotifId = notifId || null;
        document.getElementById('noteModalTitle').textContent = title.replace('Note Reminder: ', '').replace('Snoozed: ', '');
        document.getElementById('noteModalBody').textContent = message;
        document.getElementById('noteModalDoneBtn').style.display = '';
        document.getElementById('noteModalSnoozeBtn').style.display = '';
        document.getElementById('noteReminderModal').style.display = 'flex';
        document.getElementById('notificationPanel').classList.remove('show');
    }
    function noteModalDone() {
        document.getElementById('noteReminderModal').style.display = 'none';
        // Mark notification as read visually and disable it
        if (_activeNotifEl) {
            _activeNotifEl.classList.remove('unread');
            _activeNotifEl.style.opacity = '0.5';
            _activeNotifEl.style.pointerEvents = 'none';
            _activeNotifEl.style.cursor = 'default';
            var dot = _activeNotifEl.querySelector('.notif-dot');
            if (dot) dot.remove();
        }
        // Mark as read in DB so it persists after refresh
        if (_activeNotifId) {
            fetch('/notifications/' + _activeNotifId + '/read', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            });
        }
        // Update badge count
        var badge = document.querySelector('#notificationToggle span');
        if (badge) {
            var count = parseInt(badge.textContent) - 1;
            if (count <= 0) badge.remove();
            else badge.textContent = count > 9 ? '9+' : count;
        }
        // Delete the note if we have a note_id, otherwise try by title
        var noteTitle = document.getElementById('noteModalTitle').textContent;
        if (_activeNoteId && _activeNoteId > 0) {
            fetch('/notes/' + _activeNoteId + '/done', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
            }).then(r => r.json()).then(data => {
                showToast('Note marked as done.', 'success');
                var noteItem = document.querySelector('[data-note-id="' + _activeNoteId + '"]');
                if (noteItem) { noteItem.remove(); updateNotesCount(); }
                injectPastEvent(data);
            });
        } else {
            fetch('/notes/done-by-title', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ title: noteTitle })
            }).then(r => r.json()).then(data => {
                showToast('Note marked as done.', 'success');
                if (data.note_id) {
                    var noteItem = document.querySelector('[data-note-id="' + data.note_id + '"]');
                    if (noteItem) { noteItem.remove(); updateNotesCount(); }
                }
                injectPastEvent(data);
            });
        }
    }
    function noteModalSnooze() {
        document.getElementById('noteReminderModal').style.display = 'none';
        if (!_activeNoteId) return;
        fetch('/notes/' + _activeNoteId + '/snooze', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => { showToast('Will remind you again at ' + data.snooze_time + '.', 'info'); });
    }
    </script>

    <style>
        .toast {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 18px;
            border-radius: 12px;
            min-width: 280px;
            max-width: 380px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            pointer-events: all;
            animation: toastIn 0.35s cubic-bezier(0.34,1.56,0.64,1) both;
            position: relative;
            overflow: hidden;
        }
        .toast.hiding {
            animation: toastOut 0.3s ease-in forwards;
        }
        .toast-success { background: #fff; border-left: 4px solid #10b981; }
        .toast-error   { background: #fff; border-left: 4px solid #ef4444; }
        .toast-info    { background: #fff; border-left: 4px solid #2563eb; }
        .toast-warning { background: #fff; border-left: 4px solid #f59e0b; }

        .toast-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .toast-success .toast-icon { background: #d1fae5; color: #065f46; }
        .toast-error   .toast-icon { background: #fee2e2; color: #991b1b; }
        .toast-info    .toast-icon { background: #dbeafe; color: #1e40af; }
        .toast-warning .toast-icon { background: #fef3c7; color: #92400e; }

        .toast-icon svg { width: 20px; height: 20px; }

        .toast-body { flex: 1; min-width: 0; }
        .toast-title {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 2px;
        }
        .toast-msg {
            font-size: 12px;
            color: #6b7280;
            line-height: 1.4;
        }
        .toast-close {
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            font-size: 16px;
            padding: 0;
            line-height: 1;
            flex-shrink: 0;
            transition: color 0.2s;
        }
        .toast-close:hover { color: #374151; }
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            border-radius: 0 0 0 12px;
            animation: toastProgress 4s linear forwards;
        }
        .toast-success .toast-progress { background: #10b981; }
        .toast-error   .toast-progress { background: #ef4444; }
        .toast-info    .toast-progress { background: #2563eb; }
        .toast-warning .toast-progress { background: #f59e0b; }

        @keyframes toastIn {
            from { opacity: 0; transform: translateX(60px) scale(0.9); }
            to   { opacity: 1; transform: translateX(0) scale(1); }
        }
        @keyframes toastOut {
            from { opacity: 1; transform: translateX(0); max-height: 100px; margin-bottom: 0; }
            to   { opacity: 0; transform: translateX(60px); max-height: 0; padding: 0; margin: 0; }
        }
        @keyframes toastProgress {
            from { width: 100%; }
            to   { width: 0%; }
        }
    </style>

    <script>
        const TOAST_ICONS = {
            success: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
            error:   `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
            info:    `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
            warning: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`,
        };

        const TOAST_TITLES = { success: 'Success', error: 'Error', info: 'Info', warning: 'Warning' };

        function showToast(message, type = 'success', title = null) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <div class="toast-icon">${TOAST_ICONS[type] || TOAST_ICONS.info}</div>
                <div class="toast-body">
                    <div class="toast-title">${title || TOAST_TITLES[type] || 'Notice'}</div>
                    <div class="toast-msg">${message}</div>
                </div>
                <button class="toast-close" onclick="dismissToast(this.closest('.toast'))">✕</button>
                <div class="toast-progress"></div>
            `;
            container.appendChild(toast);
            setTimeout(() => dismissToast(toast), 4000);
        }

        // Global multi-keyword table search
        // Usage: multiSearch(inputValue, tableId)
        // Splits input by spaces, ALL keywords must match the row
        function multiSearch(val, tableId, countId) {
            const keywords = val.toLowerCase().split(/\s+/).filter(k => k.length > 0);
            const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
            let count = 0;
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const match = keywords.length === 0 || keywords.every(k => text.includes(k));
                row.style.display = match ? '' : 'none';
                if (match) count++;
            });
            if (countId) {
                const el = document.getElementById(countId);
                if (el) el.textContent = count + ' record' + (count !== 1 ? 's' : '');
            }
        }

        function dismissToast(toast) {
            if (!toast || toast.classList.contains('hiding')) return;
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 300);
        }

        // Global confirm dialog (replaces browser confirm())
        let _confirmCallback = null;
        function showConfirm(message, onYes, title = 'Are you sure?') {
            document.getElementById('gc-title').textContent = title;
            document.getElementById('gc-message').textContent = message;
            document.getElementById('globalConfirmDialog').style.display = 'flex';
            _confirmCallback = onYes;
        }
        function _gcYes() {
            document.getElementById('globalConfirmDialog').style.display = 'none';
            if (_confirmCallback) { _confirmCallback(); _confirmCallback = null; }
        }
        function _gcNo() {
            document.getElementById('globalConfirmDialog').style.display = 'none';
            _confirmCallback = null;
        }

        // Auto-show flash messages from Laravel session
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                showToast(@json(session('success')), 'success');
            @endif
            @if(session('error'))
                showToast(@json(session('error')), 'error');
            @endif
            @if(session('warning'))
                showToast(@json(session('warning')), 'warning');
            @endif
            @if(session('info'))
                showToast(@json(session('info')), 'info');
            @endif
            @if($errors->any())
                showToast('Please check the form for errors.', 'error', 'Validation Failed');
            @endif
        });

        // ===== SESSION TIMEOUT WARNING =====
        // 10min idle → warning toast, 20min idle → auto logout
        (function() {
            const TEN_MIN = 10 * 60 * 1000;
            let idleTimer, warned = false;
            let lastActivity = Date.now();

            function resetIdle() {
                lastActivity = Date.now();
                warned = false;
                clearTimeout(idleTimer);
                idleTimer = setTimeout(onIdle, TEN_MIN);
            }

            function onIdle() {
                if (!warned) {
                    warned = true;
                    showToast('You have been idle for 10 minutes. You will be logged out in 10 minutes if inactive.', 'warning', 'Idle Warning');
                    idleTimer = setTimeout(onIdle, TEN_MIN);
                } else {
                    showToast('Logging out due to inactivity...', 'error', 'Session Expired');
                    setTimeout(function() {
                        document.getElementById('_autoLogoutForm').submit();
                    }, 2000);
                }
            }

            ['keydown','click','scroll','touchstart','mousedown'].forEach(function(e) {
                document.addEventListener(e, resetIdle, { passive: true });
            });

            idleTimer = setTimeout(onIdle, TEN_MIN);
        })();
    </script>
    <!-- Auto logout form for session timeout -->
    <form id="_autoLogoutForm" method="POST" action="{{ route('logout') }}" style="display:none;">
        @csrf
    </form>

</body>
</html>
