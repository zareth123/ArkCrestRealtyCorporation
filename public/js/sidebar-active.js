// Sidebar Active State - Standalone JavaScript
(function() {
    'use strict';
    
    function setActiveSidebar() {
        const currentPath = window.location.pathname;
        const currentTab = new URLSearchParams(window.location.search).get('tab');
        const currentPanel = new URLSearchParams(window.location.search).get('panel') || 'profile';
        
        const navItems = document.querySelectorAll('.nav-item[data-page], .nav-subitem[data-page]');
        
        if (!navItems || navItems.length === 0) return;
        
        navItems.forEach(item => item.classList.remove('active'));
        
        navItems.forEach(item => {
            const page = item.getAttribute('data-page');
            let isActive = false;
            
            if (page === 'dashboard' && currentPath === '/dashboard') {
                isActive = true;
            } else if (page === 'departments' && currentPath.includes('/departments')) {
                isActive = true;
            } else if (page === 'summary-report' && currentPath.includes('/summary-report')) {
                isActive = true;
            } else if (page === 'sales-marketing' && currentPath.includes('/sales-marketing')) {
                isActive = true;
            } else if (page === 'commission-monitoring' && currentPath.includes('/commission-monitoring')) {
                isActive = true;
            } else if (page === 'commission-dashboard' && currentPath.includes('/commission-dashboard')) {
                isActive = true;
            } else if (page === 'calendar' && currentPath === '/calendar') {
                isActive = true;
            } else if (page === 'client-database' && currentPath.includes('/client-database')) {
                isActive = true;
            } else if (page === 'site-visit-database' && currentPath.includes('/site-visit-database')) {
                isActive = true;
            } else if (page === 'cd-clients' && currentPath.includes('/reserved-clients')) {
                isActive = true;
            } else if (page === 'sm-calendar' && currentPath.includes('/sales-calendar')) {
                isActive = true;
            } else if (page === 'crm' && currentPath.includes('/crm')) {
                isActive = true;
            } else if (page === 'forms-budget' && currentPath === '/forms' && currentTab !== 'site-visit') {
                isActive = true;
            } else if (page === 'forms-site-visit' && ((currentPath === '/forms' && currentTab === 'site-visit') || currentPath.includes('/forms/site-visit'))) {
                isActive = true;
            } else if (page === 'arkcrest-sales' && currentPath.includes('/arkcrest-sales')) {
                isActive = true;
            } else if (page === 'forms' && currentPath === '/forms') {
                isActive = true;
            } else if (page === 'settings' && currentPath.includes('/settings')) {
                isActive = true;
            } else if (page === 'settings-profile' && currentPath.includes('/settings') && currentPanel === 'profile') {
                isActive = true;
            } else if (page === 'settings-employee-info' && currentPath.includes('/settings') && currentPanel === 'employee-info') {
                isActive = true;
            } else if (page === 'settings-system' && currentPath.includes('/settings') && currentPanel === 'system') {
                isActive = true;
            } else if (page === 'settings-notes' && currentPath.includes('/settings') && currentPanel === 'notes') {
                isActive = true;
            } else if (page === 'settings-privacy' && currentPath.includes('/settings') && currentPanel === 'privacy') {
                isActive = true;
            } else if (page === 'settings-users' && currentPath.includes('/settings') && currentPanel === 'users') {
                isActive = true;
            } else if (page === 'settings-visibility' && currentPath.includes('/settings') && currentPanel === 'visibility') {
                isActive = true;
            } else if (page === 'settings-activity' && currentPath.includes('/settings') && currentPanel === 'activity') {
                isActive = true;
            } else if (page === 'settings-deleted' && currentPath.includes('/settings') && currentPanel === 'deleted') {
                isActive = true;
            } else if (page === 'settings-permission-requests' && currentPath.includes('/settings') && currentPanel === 'permission-requests') {
                isActive = true;
            } else if (page === 'settings-teams' && currentPath.includes('/settings') && currentPanel === 'teams') {
                isActive = true;
            } else if (page === 'settings-properties' && currentPath.includes('/settings') && currentPanel === 'properties') {
                isActive = true;
            } else if (page === 'settings-period-lock' && currentPath.includes('/settings') && currentPanel === 'period-lock') {
                isActive = true;
            } else if (page === 'human-resource' && currentPath === '/human-resource') {
                isActive = true;
            } else if (page === 'hr-employee-data' && currentPath.includes('/human-resource/employee-data')) {
                isActive = true;
            } else if (page === 'hr-contact-list' && currentPath.includes('/human-resource/contact-list')) {
                isActive = true;
            } else if (page === 'cd-properties' && currentPath.includes('/property-list')) {
                isActive = true;
            } else if (page === 'settings-edit-history' && currentPath.includes('/settings/edit-history')) {
                isActive = true;
            } else if (page === 'settings-backup' && currentPath.includes('/settings/backup')) {
                isActive = true;
            } else if (page === 'settings-export' && currentPath.includes('/admin/export')) {
                isActive = true;
            }
            
            if (isActive) {
                item.classList.add('active');
                
                // If this is a subitem, open its parent dropdown
                if (item.classList.contains('nav-subitem')) {
                    const submenu = item.closest('.nav-submenu');
                    if (submenu) {
                        submenu.classList.add('open');
                        // Determine which dropdown this belongs to
                        if (submenu.id === 'salesSubmenu') {
                            const arrow = document.getElementById('salesArrow');
                            if (arrow) arrow.classList.add('open');
                            localStorage.setItem('salesDropdownOpen', 'true');
                        } else if (submenu.id === 'clientDbSubmenu') {
                            const arrow = document.getElementById('clientDbArrow');
                            if (arrow) arrow.classList.add('open');
                        } else if (submenu.id === 'commissionSubmenu') {
                            const arrow = document.getElementById('commissionArrow');
                            if (arrow) arrow.classList.add('open');
                        } else if (submenu.id === 'hrSubmenu') {
                            const arrow = document.getElementById('hrArrow');
                            if (arrow) arrow.classList.add('open');
                        } else if (submenu.id === 'formsSubmenu') {
                            const arrow = document.getElementById('formsArrow');
                            if (arrow) arrow.classList.add('open');
                            localStorage.setItem('formsDropdownOpen', 'true');
                        } else if (submenu.id === 'settingsSubmenu') {
                            const arrow = document.getElementById('settingsArrow');
                            if (arrow) arrow.classList.add('open');
                            localStorage.setItem('settingsDropdownOpen', 'true');
                        } else {
                            const arrow = document.getElementById('financeArrow');
                            if (arrow) arrow.classList.add('open');
                            localStorage.setItem('financeDropdownOpen', 'true');
                        }
                    }
                }
            }
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setActiveSidebar);
    } else {
        setActiveSidebar();
    }
    
    setTimeout(setActiveSidebar, 100);
})();