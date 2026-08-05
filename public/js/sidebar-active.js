// Sidebar Active State - Standalone JavaScript
(function() {
    'use strict';

    function setDropdownState(submenuId, arrowId, storageKey, isOpen) {
        const submenu = document.getElementById(submenuId);
        const arrow = document.getElementById(arrowId);

        if (submenu) submenu.classList.toggle('open', isOpen);
        if (arrow) arrow.classList.toggle('open', isOpen);
        if (storageKey) localStorage.setItem(storageKey, isOpen ? 'true' : 'false');
    }

    function setActiveSidebar() {
        const currentPath = window.location.pathname;
        const params = new URLSearchParams(window.location.search);
        const currentTab = params.get('tab');
        const currentPanel = params.get('panel') || 'profile';
        const isSettingsIndexPage = /\/settings\/?$/.test(currentPath);

        const accountPanels = ['profile', 'employee-info', 'system', 'notes', 'privacy'];
        const adminPanels = [
            'users',
            'visibility',
            'activity',
            'deleted',
            'teams',
            'properties',
            'period-lock',
            'permission-requests'
        ];

        const isAccountSettingsPage = isSettingsIndexPage && accountPanels.includes(currentPanel);
        const isAdminSettingsPage = isSettingsIndexPage && adminPanels.includes(currentPanel);
        const isPracticeAdminPage = currentPath.includes('/practice/admin');
        const isEditHistoryPage = currentPath.includes('/settings/edit-history');
        const isBackupPage = currentPath.includes('/settings/backup');
        const isExportPage = currentPath.includes('/admin/export');
        const isNewsUpdatesPostingPage = currentPath.includes('/admin/news-updates');
        const isAdminPage = isAdminSettingsPage
            || isPracticeAdminPage
            || isEditHistoryPage
            || isBackupPage
            || isExportPage
            || isNewsUpdatesPostingPage;

        const navItems = document.querySelectorAll('.nav-item[data-page], .nav-subitem[data-page]');
        if (!navItems || navItems.length === 0) return;

        // Prevent the two parent buttons from being active/open at the same time.
        if (isAdminPage) {
            setDropdownState('settingsSubmenu', 'settingsArrow', 'settingsDropdownOpen', false);
        } else if (isAccountSettingsPage) {
            setDropdownState('adminSubmenu', 'adminArrow', 'adminDropdownOpen', false);
            const adminTrigger = document.querySelector('[data-page="admin"]');
            if (adminTrigger) adminTrigger.setAttribute('aria-expanded', 'false');
        }

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
            } else if (page === 'cash-advance' && currentPath.includes('/cash-advance') && !currentPath.includes('/agent-cash-advance')) {
                isActive = true;
            } else if (page === 'agent-cash-advance' && currentPath.includes('/agent-cash-advance')) {
                isActive = true;
            } else if (page === 'forms' && currentPath === '/forms') {
                isActive = true;
            } else if (page === 'settings' && isAccountSettingsPage) {
                isActive = true;
            } else if (page === 'admin' && isAdminPage) {
                isActive = true;
            } else if (page === 'settings-profile' && isAccountSettingsPage && currentPanel === 'profile') {
                isActive = true;
            } else if (page === 'settings-employee-info' && isAccountSettingsPage && currentPanel === 'employee-info') {
                isActive = true;
            } else if (page === 'settings-system' && isAccountSettingsPage && currentPanel === 'system') {
                isActive = true;
            } else if (page === 'settings-notes' && isAccountSettingsPage && currentPanel === 'notes') {
                isActive = true;
            } else if (page === 'settings-privacy' && isAccountSettingsPage && currentPanel === 'privacy') {
                isActive = true;
            } else if (page === 'settings-users' && isAdminSettingsPage && currentPanel === 'users') {
                isActive = true;
            } else if (page === 'settings-visibility' && isAdminSettingsPage && currentPanel === 'visibility') {
                isActive = true;
            } else if (page === 'settings-activity' && isAdminSettingsPage && currentPanel === 'activity') {
                isActive = true;
            } else if (page === 'settings-deleted' && isAdminSettingsPage && currentPanel === 'deleted') {
                isActive = true;
            } else if (page === 'settings-permission-requests' && isAdminSettingsPage && currentPanel === 'permission-requests') {
                isActive = true;
            } else if (page === 'settings-teams' && isAdminSettingsPage && currentPanel === 'teams') {
                isActive = true;
            } else if (page === 'settings-properties' && isAdminSettingsPage && currentPanel === 'properties') {
                isActive = true;
            } else if (page === 'settings-period-lock' && isAdminSettingsPage && currentPanel === 'period-lock') {
                isActive = true;
            } else if (page === 'human-resource' && currentPath === '/human-resource') {
                isActive = true;
            } else if (page === 'hr-employee-data' && currentPath.includes('/human-resource/employee-data')) {
                isActive = true;
            } else if (page === 'hr-contact-list' && currentPath.includes('/human-resource/contact-list')) {
                isActive = true;
            } else if (page === 'cd-properties' && currentPath.includes('/property-list')) {
                isActive = true;
            } else if (page === 'settings-edit-history' && isEditHistoryPage) {
                isActive = true;
            } else if (page === 'settings-backup' && isBackupPage) {
                isActive = true;
            } else if (page === 'settings-export' && isExportPage) {
                isActive = true;
            } else if (page === 'admin-news-updates' && isNewsUpdatesPostingPage) {
                isActive = true;
            } else if (page === 'practice-admin' && isPracticeAdminPage && !currentPath.includes('/practice/admin/history')) {
                isActive = true;
            } else if (page === 'practice-admin-history' && currentPath.includes('/practice/admin/history')) {
                isActive = true;
            }

            if (!isActive) return;

            item.classList.add('active');

            if (page === 'admin') {
                setDropdownState('adminSubmenu', 'adminArrow', 'adminDropdownOpen', true);
                item.setAttribute('aria-expanded', 'true');
            }

            // If this is a subitem, open its correct parent dropdown.
            if (item.classList.contains('nav-subitem')) {
                const submenu = item.closest('.nav-submenu');
                if (!submenu) return;

                submenu.classList.add('open');

                if (submenu.id === 'salesSubmenu') {
                    setDropdownState('salesSubmenu', 'salesArrow', 'salesDropdownOpen', true);
                } else if (submenu.id === 'clientDbSubmenu') {
                    setDropdownState('clientDbSubmenu', 'clientDbArrow', null, true);
                } else if (submenu.id === 'commissionSubmenu') {
                    setDropdownState('commissionSubmenu', 'commissionArrow', null, true);
                } else if (submenu.id === 'cashAdvanceSubmenu') {
                    setDropdownState('cashAdvanceSubmenu', 'cashAdvanceArrow', null, true);
                } else if (submenu.id === 'hrSubmenu') {
                    setDropdownState('hrSubmenu', 'hrArrow', null, true);
                } else if (submenu.id === 'formsSubmenu') {
                    setDropdownState('formsSubmenu', 'formsArrow', 'formsDropdownOpen', true);
                } else if (submenu.id === 'settingsSubmenu') {
                    setDropdownState('settingsSubmenu', 'settingsArrow', 'settingsDropdownOpen', true);
                } else if (submenu.id === 'adminSubmenu') {
                    setDropdownState('adminSubmenu', 'adminArrow', 'adminDropdownOpen', true);
                    const adminTrigger = document.querySelector('[data-page="admin"]');
                    if (adminTrigger) {
                        adminTrigger.classList.add('active');
                        adminTrigger.setAttribute('aria-expanded', 'true');
                    }
                } else {
                    setDropdownState('financeSubmenu', 'financeArrow', 'financeDropdownOpen', true);
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
