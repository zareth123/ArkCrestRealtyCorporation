<?php

namespace App\Http\Controllers;

use App\Models\PersuasionScenario;
use Illuminate\Http\Request;

class PersuasionScenarioAdminController extends Controller
{
    /** List + manage all scenarios (active and inactive). */
    public function index(Request $request)
    {
        $scenarios = PersuasionScenario::orderByRaw("FIELD(difficulty, 'EASY','MEDIUM','HARD')")
            ->orderBy('name')
            ->get();

        return view('practice.admin.index', compact('scenarios'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        PersuasionScenario::create($data);

        return redirect()->route('practice.admin')->with('success', 'Scenario created.');
    }

    public function update(Request $request, PersuasionScenario $scenario)
    {
        $scenario->update($this->validated($request));

        return redirect()->route('practice.admin')->with('success', 'Scenario updated.');
    }

    public function destroy(PersuasionScenario $scenario)
    {
        // Soft delete only — keeps historical sessions/scores intact for
        // any agent who already practiced against this scenario.
        $scenario->delete();

        return redirect()->route('practice.admin')->with('success', 'Scenario removed.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'                => 'required|string|max:150',
            'tagline'             => 'nullable|string|max:200',
            'difficulty'          => 'required|in:EASY,MEDIUM,HARD',
            'buyer_name'          => 'required|string|max:100',
            'buyer_backstory'     => 'nullable|string',
            'buyer_budget'        => 'nullable|numeric|min:0',
            'personality_traits'  => 'nullable|string',
            'common_objections'   => 'nullable|string',
            'win_conditions'      => 'nullable|string',
            'walkaway_triggers'   => 'nullable|string',
            'is_active'           => 'nullable|boolean',
        ]);

        // Checkbox: absent from the request means unchecked/false.
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}