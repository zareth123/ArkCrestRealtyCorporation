<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SalesAgent;
use App\Models\PersonnelContact;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class HumanResourceController extends Controller
{
    public function index()
    {
        $salesPositions = ['sales agent', 'sales manager', 'sales person', 'salesperson', 'sales team leader', 'sales personnel'];

        $totalEmployees = User::whereIn('status', ['active', 'pre_registered', 'pending'])
            ->whereNotNull('employee_id')
            ->count();

        $totalAgents = User::whereIn('status', ['active', 'pre_registered', 'pending'])
            ->whereNotNull('position')
            ->whereIn(\DB::raw('LOWER(TRIM(position))'), $salesPositions)
            ->count();

        return view('human-resource', compact('totalEmployees', 'totalAgents'));
    }

    public function employeeData()
    {
        $activeUsers = User::whereIn('status', ['active', 'pre_registered', 'pending'])
            ->orderBy('employee_id')
            ->get();

        return view('hr-employee-data', compact('activeUsers'));
    }

    public function contactList()
    {
        $personnelContacts = Schema::hasColumn('personnel_contacts', 'sort_order')
            ? PersonnelContact::orderBy('sort_order')->orderBy('id')->get()
            : PersonnelContact::orderBy('id')->get();

        return view('hr-contact-list', compact('personnelContacts'));
    }
}
