/**
 * table-sort.js
 * Adds ONE toggle button (Ascending <-> Descending) next to the Filter/Search
 * controls of specific record tables — sorting by the "#" (or first data)
 * column. Only tables explicitly marked with class="js-sort-table" in their
 * Blade file are affected — this is intentional, so the button only shows
 * up on the approved list of tables, nowhere else.
 */
(function () {
    // Inject styles once — a single, consistent, self-contained design
    // used site-wide, independent of each page's own button styling.
    if (!document.getElementById('idxSortStyles')) {
        var style = document.createElement('style');
        style.id = 'idxSortStyles';
        style.textContent = `
            .idx-sort-toggle{
                display:inline-flex;align-items:center;justify-content:center;gap:6px;
                white-space:nowrap;font-size:13px;font-weight:600;font-family:inherit;
                color:#fff;background:linear-gradient(135deg,#1e4575,#2563eb);
                border:none;border-radius:8px;padding:0 14px;cursor:pointer;
                height:36px;min-width:36px;box-sizing:border-box;
                transition:filter .15s ease, transform .1s ease;
                flex-shrink:0;
            }
            .idx-sort-toggle:hover{filter:brightness(1.1);}
            .idx-sort-toggle:active{transform:scale(.95);}
            .idx-sort-toggle .idx-sort-icon{font-size:13px;line-height:1;}
            .idx-sort-toolbar-fallback{padding:10px 18px 0;display:flex;flex-wrap:wrap;}

            @media (max-width:600px){
                .idx-sort-toggle{
                    padding:0;width:38px;height:38px;min-width:38px;
                    border-radius:50%;
                }
                .idx-sort-toggle .idx-sort-text{display:none;}
                .idx-sort-toggle .idx-sort-icon{font-size:15px;}
            }
        `;
        document.head.appendChild(style);
    }

    function getIndexColumn(table) {
        var headerRow = table.querySelector('thead tr');
        if (!headerRow) return -1;
        var ths = Array.prototype.slice.call(headerRow.querySelectorAll('th'));

        // Prefer an explicit "#" index column when one exists.
        var hashIdx = ths.findIndex(function (th) {
            return th.textContent.trim() === '#';
        });
        if (hashIdx !== -1) return hashIdx;

        // Otherwise fall back to the first column that actually has a label
        // and isn't a checkbox/action column (e.g. "Date" on department pages).
        return ths.findIndex(function (th) {
            var hasControl = !!th.querySelector('input, button, select');
            var hasLabel = th.textContent.trim() !== '';
            return hasLabel && !hasControl;
        });
    }

    // Find the nearest element that is a structural ancestor of BOTH nodes.
    function commonAncestor(a, b) {
        var ancestors = [];
        for (var el = a; el; el = el.parentElement) ancestors.push(el);
        for (var el2 = b; el2; el2 = el2.parentElement) {
            if (ancestors.indexOf(el2) !== -1) return el2;
        }
        return null;
    }

    function isHidden(el) {
        return window.getComputedStyle(el).display === 'none';
    }

    // Look for the nearest preceding sibling container that holds the
    // page's search box and/or filter button, so our toggle can sit beside them.
    function findToolbar(table) {
        var el = table.closest('[class*="table-wrap"], [class*="table-container"], [class*="tbl-wrap"]') || table.parentElement;
        var hops = 6;

        while (el && hops-- > 0) {
            var parent = el.parentElement;
            if (!parent) break;

            var siblings = Array.prototype.slice.call(parent.children);
            var idx = siblings.indexOf(el);
            var preceding = siblings.slice(0, idx).filter(function (s) {
                return s instanceof HTMLElement && !isHidden(s);
            });

            // Pass 1: real search/filter controls take priority, regardless
            // of which sibling is closest — this avoids matching an empty,
            // hidden "active filters" placeholder that merely shares a
            // similar class name.
            for (var i = preceding.length - 1; i >= 0; i--) {
                var sib = preceding[i];
                var searchInput = sib.querySelector('input[type="text"], input[type="search"]');
                var filterBtn = sib.querySelector('[class*="filter-btn"]');

                if (searchInput && filterBtn) {
                    var row = commonAncestor(searchInput, filterBtn);
                    if (row) return row;
                }
                if (searchInput || filterBtn) {
                    return (searchInput || filterBtn).parentElement;
                }
            }

            // Pass 2: fall back to a container that merely looks like a
            // toolbar by class name (only if pass 1 found nothing).
            for (var j = preceding.length - 1; j >= 0; j--) {
                var sib2 = preceding[j];
                if (/filter|search|toolbar/i.test(sib2.className || '')) {
                    return sib2;
                }
            }

            el = parent;
        }
        return null;
    }

    // Strict numeric parse — unlike parseFloat, rejects strings like
    // "2026-05-01" (which parseFloat would read as just 2026).
    function parseStrictNumber(text) {
        var cleaned = text.replace(/[₱$,]/g, '').trim();
        if (cleaned === '' || /[a-zA-Z]/.test(cleaned)) return null;
        var num = Number(cleaned);
        return isNaN(num) ? null : num;
    }

    function sortTable(table, colIndex, direction) {
        var tbody = table.querySelector('tbody');
        if (!tbody) return;

        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'))
            .filter(function (r) { return r.children.length > colIndex; });
        if (!rows.length) return;

        rows.sort(function (a, b) {
            var aText = (a.children[colIndex].textContent || '').trim();
            var bText = (b.children[colIndex].textContent || '').trim();
            var aNum = parseStrictNumber(aText);
            var bNum = parseStrictNumber(bText);
            var cmp;
            if (aNum !== null && bNum !== null) {
                cmp = aNum - bNum;
            } else {
                // Works well for text and for ISO-style dates (YYYY-MM-DD)
                cmp = aText.localeCompare(bText, undefined, { numeric: true, sensitivity: 'base' });
            }
            return direction === 'asc' ? cmp : -cmp;
        });

        var frag = document.createDocumentFragment();
        rows.forEach(function (r) { frag.appendChild(r); });
        tbody.appendChild(frag);
    }

    function createToggleButton(columnLabel) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'idx-sort-toggle';
        btn.dataset.state = 'asc';
        btn.title = 'Sort by "' + columnLabel + '"';
        btn.innerHTML =
            '<span class="idx-sort-icon">&#8593;</span>' +
            '<span class="idx-sort-text">Sort</span>';
        return btn;
    }

    function initTable(table) {
        if (table.dataset.idxSortToggleInit) return;

        var colIndex = getIndexColumn(table);
        if (colIndex === -1) return;

        table.dataset.idxSortToggleInit = '1';

        var headerRow = table.querySelector('thead tr');
        var ths = Array.prototype.slice.call(headerRow.querySelectorAll('th'));
        var columnLabel = ths[colIndex].textContent.trim() || 'Column';

        var btn = createToggleButton(columnLabel);
        var toolbar = findToolbar(table);
        if (toolbar && isHidden(toolbar)) toolbar = null;

        if (toolbar) {
            toolbar.appendChild(btn);
        } else {
            var fallback = document.createElement('div');
            fallback.className = 'idx-sort-toolbar-fallback';
            fallback.appendChild(btn);
            table.parentElement.insertBefore(fallback, table);
        }

        btn.addEventListener('click', function () {
            var next = btn.dataset.state === 'asc' ? 'desc' : 'asc';
            btn.dataset.state = next;
            btn.querySelector('.idx-sort-icon').innerHTML = next === 'asc' ? '&#8593;' : '&#8595;';
            sortTable(table, colIndex, next);
        });
    }

    function scanAllTables() {
        document.querySelectorAll('table.js-sort-table').forEach(initTable);
    }

    document.addEventListener('DOMContentLoaded', scanAllTables);

    // Some tables (e.g. client-database) load their rows via AJAX after
    // page load. Watch for new tables being added and wire them up too.
    var rescanTimer = null;
    var observer = new MutationObserver(function () {
        clearTimeout(rescanTimer);
        rescanTimer = setTimeout(scanAllTables, 200);
    });
    observer.observe(document.body, { childList: true, subtree: true });
})();