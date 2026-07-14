<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommissionRequest;
use App\Models\CommissionRequestSales;
use App\Models\Department;
use App\Models\DepartmentalExpense;
use App\Models\SummaryReport;
use App\Models\TripSchedule;
use App\Models\Note;
use App\Models\HrForm;
use App\Models\PersonnelContact;
use App\Models\Client;
use App\Models\Property;
use App\Models\SalesAgent;
use App\Models\ReservedClient;
use App\Models\User;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));
        if (strlen($query) < 1) return response()->json([]);

        $user    = auth()->user();
        $hidden  = $user->hidden_pages ?? [];
        $results = [];
        $isAdmin = $user->isAdmin();

        $canSee = fn(string $page) => !in_array($page, $hidden);
        $like   = "%{$query}%";

        if ($canSee('commission-monitoring')) {
            try {
                CommissionRequest::where(function ($q) use ($like) {
                    $q->where('control_number',   'like', $like)
                      ->orWhere('requestor_name', 'like', $like)
                      ->orWhere('department',     'like', $like)
                      ->orWhere('category',       'like', $like)
                      ->orWhere('status',         'like', $like)
                      ->orWhere('client_name',    'like', $like)
                      ->orWhere('agent_name',     'like', $like)
                      ->orWhere('project_name',   'like', $like)
                      ->orWhere('remarks',        'like', $like);
                })->orderBy('date_requested', 'desc')->limit(15)->get()
                ->each(function ($r) use (&$results) {
                    $results[] = [
                        'type'         => 'commission',
                        'title'        => ($r->control_number ?? 'No CN') . ' — ' . ($r->requestor_name ?? ''),
                        'description'  => ($r->department ?? '') . ' | ' . ($r->category ?? '') . ' | ' . ($r->date_requested ? $r->date_requested->format('M d, Y') : ''),
                        'amount'       => $r->requested_amount ? '₱' . number_format($r->requested_amount, 2) : null,
                        'status'       => $r->status,
                        'url'          => '/commission-monitoring',
                        'highlight_id' => 'cm-' . $r->id,
                        'icon'         => 'document',
                    ];
                });
            } catch (\Exception $e) {}
        }

        if ($canSee('client-database')) {
            try {
                CommissionRequestSales::where(function ($q) use ($like) {
                    $q->where('client_name',       'like', $like)
                      ->orWhere('agent_name',      'like', $like)
                      ->orWhere('project_name',    'like', $like)
                      ->orWhere('developer_name',  'like', $like)
                      ->orWhere('block_lot_number','like', $like)
                      ->orWhere('terms_of_payment','like', $like)
                      ->orWhere('client_status',   'like', $like)
                      ->orWhere('remarks',         'like', $like);
                })->orderBy('date_requested', 'desc')->limit(15)->get()
                ->each(function ($r) use (&$results) {
                    $results[] = [
                        'type'         => 'client',
                        'title'        => $r->client_name ?? '',
                        'description'  => ($r->project_name ?? '') . ' | ' . ($r->agent_name ?? '') . ' | ' . ($r->developer_name ?? ''),
                        'amount'       => $r->net_tcp ? '₱' . number_format($r->net_tcp, 2) : null,
                        'status'       => $r->client_status ?: $r->status,
                        'url'          => '/client-database',
                        'highlight_id' => 'cd-' . $r->id,
                        'icon'         => 'person',
                    ];
                });
            } catch (\Exception $e) {}
        }

        if ($canSee('site-visit-database')) {
            try {

                TripSchedule::where(function ($q) use ($like) {
                    $q->where('client_name',    'like', $like)
                      ->orWhere('agent_name',   'like', $like)
                      ->orWhere('property_name','like', $like)
                      ->orWhere('status',       'like', $like);
                })->orderBy('tripping_date', 'desc')->limit(10)->get()
                ->each(function ($r) use (&$results) {
                    $results[] = [
                        'type'         => 'trip',
                        'title'        => $r->client_name ?? '',
                        'description'  => ($r->property_name ?? '') . ' | ' . ($r->agent_name ?? '') . ' | ' . ($r->tripping_date ? $r->tripping_date->format('M d, Y') : ''),
                        'status'       => $r->status,
                        'url'          => '/site-visit-database',
                        'highlight_id' => 'trip-' . $r->id,
                        'icon'         => 'location',
                    ];
                });
            } catch (\Exception $e) {}
        }

        if ($canSee('departments')) {
            try {
                DepartmentalExpense::where(function ($q) use ($like) {
                    $q->where('control_number',  'like', $like)
                      ->orWhere('requestor_name','like', $like)
                      ->orWhere('department',    'like', $like)
                      ->orWhere('category',      'like', $like)
                      ->orWhere('status',        'like', $like);
                })->orderBy('date_requested', 'desc')->limit(10)->get()
                ->each(function ($r) use (&$results) {
                    $results[] = [
                        'type'         => 'expense',
                        'title'        => ($r->control_number ?? 'No CN') . ' — ' . ($r->requestor_name ?? ''),
                        'description'  => ($r->department ?? '') . ' | ' . ($r->category ?? '') . ' | ' . ($r->date_requested ? $r->date_requested->format('M d, Y') : ''),
                        'amount'       => $r->requested_amount ? '₱' . number_format($r->requested_amount, 2) : null,
                        'status'       => $r->status,
                        'url'          => '/departments',
                        'highlight_id' => 'expense-' . $r->id,
                        'icon'         => 'building',
                    ];
                });
            } catch (\Exception $e) {}
        }

        if ($canSee('summary-report')) {
            try {
                $months = ['','January','February','March','April','May','June','July','August','September','October','November','December'];
                $matchingMonths = array_keys(array_filter($months, fn($m) => $m && stripos($m, $query) !== false));

                SummaryReport::where(function ($q) use ($like, $matchingMonths) {
                    $q->whereRaw('CAST(year AS CHAR) LIKE ?', [$like]);
                    if (!empty($matchingMonths)) {
                        $q->orWhereIn('month', $matchingMonths);
                    }
                })->limit(8)->get()
                ->each(function ($r) use (&$results, $months) {
                    $results[] = [
                        'type'        => 'report',
                        'title'       => 'Summary Report — ' . ($months[$r->month] ?? $r->month) . ' ' . $r->year,
                        'description' => 'Units: ' . ($r->units ?? 0) . ' | Gross Sales: ₱' . number_format($r->gross_sales ?? 0, 2),
                        'url'         => '/summary-report',
                        'icon'        => 'chart',
                    ];
                });
            } catch (\Exception $e) {}
        }

        if ($canSee('human-resource')) {
            try {
                HrForm::where(function ($q) use ($like) {
                    $q->where('title', 'like', $like)
                      ->orWhere('type', 'like', $like);
                })->orderBy('created_at', 'desc')->limit(8)->get()
                ->each(function ($r) use (&$results) {
                    $results[] = [
                        'type'        => 'hrform',
                        'title'       => $r->title ?? HrForm::typeLabel($r->type),
                        'description' => HrForm::typeLabel($r->type) . ' | ' . ($r->created_at ? $r->created_at->format('M d, Y') : ''),
                        'url'         => '/human-resource',
                        'icon'        => 'document',
                    ];
                });
            } catch (\Exception $e) {}
        }

        try {
            Note::where('user_id', $user->id)
                ->whereNull('completed_at')
                ->where(function ($q) use ($like) {
                    $q->where('title', 'like', $like)
                      ->orWhere('body', 'like', $like);
                })->orderBy('note_date', 'desc')->limit(5)->get()
            ->each(function ($r) use (&$results) {
                $results[] = [
                    'type'        => 'note',
                    'title'       => $r->title ?? '',
                    'description' => ($r->body ? \Str::limit($r->body, 60) : '') . ($r->note_date ? ' | ' . $r->note_date->format('M d, Y') : ''),
                    'url'         => '#notes',
                    'icon'        => 'note',
                ];
            });
        } catch (\Exception $e) {}

        if ($canSee('human-resource')) {
            try {
                PersonnelContact::where(function ($q) use ($like) {
                    $q->where('name',     'like', $like)
                      ->orWhere('company','like', $like)
                      ->orWhere('email',  'like', $like)
                      ->orWhere('phone',  'like', $like);
                })->limit(8)->get()
                ->each(function ($r) use (&$results) {
                    $results[] = [
                        'type'         => 'contact',
                        'title'        => $r->name ?? '',
                        'description'  => ($r->company ?? '') . ($r->email ? ' | ' . $r->email : '') . ($r->phone ? ' | ' . $r->phone : ''),
                        'url'          => '/human-resource/contact-list',
                        'highlight_id' => 'contact-' . $r->id,
                        'icon'         => 'person',
                    ];
                });
            } catch (\Exception $e) {}
        }

        if ($canSee('client-database')) {
            try {
                Client::where(function ($q) use ($like) {
                    $q->where('name',     'like', $like)
                      ->orWhere('address','like', $like);
                })->limit(8)->get()
                ->each(function ($r) use (&$results) {
                    $results[] = [
                        'type'        => 'clientinfo',
                        'title'       => $r->name ?? '',
                        'description' => $r->address ?? 'Client contact info',
                        'url'         => '/reserved-clients',
                        'icon'        => 'person',
                    ];
                });
            } catch (\Exception $e) {}
        }

        if ($canSee('client-database')) {
            try {
                ReservedClient::where(function ($q) use ($like) {
                    $q->where('client_name',    'like', $like)
                      ->orWhere('agent_name',   'like', $like)
                      ->orWhere('property_name','like', $like)
                      ->orWhere('company_name', 'like', $like);
                })->limit(8)->get()
                ->each(function ($r) use (&$results) {
                    $results[] = [
                        'type'        => 'reserved',
                        'title'       => $r->client_name ?? '',
                        'description' => ($r->property_name ?? '') . ' | ' . ($r->agent_name ?? ''),
                        'url'         => '/reserved-clients',
                        'icon'        => 'person',
                    ];
                });
            } catch (\Exception $e) {}
        }

        try {
            Property::where(function ($q) use ($like) {
                $q->where('name',      'like', $like)
                  ->orWhere('developer','like', $like);
            })->limit(6)->get()
            ->each(function ($r) use (&$results) {
                $results[] = [
                    'type'        => 'property',
                    'title'       => $r->name ?? '',
                    'description' => 'Developer: ' . ($r->developer ?? 'N/A'),
                    'url'         => '/client-database',
                    'icon'        => 'building',
                ];
            });
        } catch (\Exception $e) {}

        if ($canSee('sales-marketing')) {
            try {
                SalesAgent::where(function ($q) use ($like) {
                    $q->where('name',        'like', $like)
                      ->orWhere('employee_id','like', $like);
                })->where('is_active', true)->limit(8)->get()
                ->each(function ($r) use (&$results) {
                    $results[] = [
                        'type'        => 'agent',
                        'title'       => $r->name ?? '',
                        'description' => 'Sales Agent' . ($r->employee_id ? ' | ID: ' . $r->employee_id : ''),
                        'url'         => '/sales-marketing',
                        'icon'        => 'person',
                    ];
                });
            } catch (\Exception $e) {}
        }

        if ($isAdmin) {
            try {
                User::where(function ($q) use ($like) {
                    $q->where('name',         'like', $like)
                      ->orWhere('email',      'like', $like)
                      ->orWhere('position',   'like', $like)
                      ->orWhere('employee_id','like', $like);
                })->limit(8)->get()
                ->each(function ($r) use (&$results) {
                    $results[] = [
                        'type'         => 'user',
                        'title'        => $r->name ?? '',
                        'description'  => ($r->email ?? '') . ($r->position ? ' | ' . $r->position : '') . ' | ' . ucfirst($r->role ?? 'staff'),
                        'url'          => '/human-resource/employee-data',
                        'highlight_id' => 'emp-view-' . $r->id,
                        'icon'         => 'person',
                    ];
                });
            } catch (\Exception $e) {}
        }

        $allPages = [
            ['Finance Dashboard',        '/dashboard',                   'home',     'dashboard'],
            ['Departmental Expenses',     '/departments',                 'building', 'departments'],
            ['Summary Report',           '/summary-report',              'chart',    'summary-report'],
            ['Commission Monitoring',    '/commission-monitoring',        'document', 'commission-monitoring'],
            ['Commission Dashboard',     '/commission-dashboard',         'chart',    'commission-monitoring'],
            ['ARC Sales',                '/arkcrest-sales',              'chart',    'arkcrest-sales'],
            ['Cash Advance',             '/cash-advance',                'document', 'cash-advance'],
            ['Calendar',                 '/calendar',                    'calendar', 'calendar'],
            ['Sales & Marketing',        '/sales-marketing',             'chart',    'sales-marketing'],
            ['Client Database',          '/client-database',             'person',   'client-database'],
            ['Site Visit Database',      '/site-visit-database',         'location', 'site-visit-database'],
            ['Sales Calendar',           '/sales-calendar',              'calendar', 'sales-calendar'],
            ['Reserved Clients',         '/reserved-clients',            'person',   'client-database'],
            ['Human Resource',           '/human-resource',              'person',   'human-resource'],
            ['HR Employee Data',         '/human-resource/employee-data','person',   'human-resource'],
            ['HR Contact List',          '/human-resource/contact-list', 'person',   'human-resource'],
            ['Forms',                    '/forms',                       'document', 'forms'],
            ['Settings',                 '/settings',                    'settings', 'settings'],
            ['Property List',            '/property-list',               'building', 'sales-marketing'],
        ];

        foreach ($allPages as [$title, $url, $icon, $pageKey]) {
            if ($canSee($pageKey) && stripos($title, $query) !== false) {
                $results[] = [
                    'type'        => 'page',
                    'title'       => $title,
                    'description' => 'Go to ' . $title,
                    'url'         => $url,
                    'icon'        => $icon,
                ];
            }
        }

        return response()->json(array_slice($results, 0, 60));
    }
}
