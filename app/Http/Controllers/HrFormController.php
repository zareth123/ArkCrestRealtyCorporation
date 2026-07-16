<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HrForm;

class HrFormController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type'  => 'required|in:dayoff,absences,voucher',
            'title' => 'nullable|string|max:255',
            'data'  => 'required|array',
        ]);

        // Auto-create table if migration hasn't run
        if (!\Schema::hasTable('hr_forms')) {
            \Schema::create('hr_forms', function ($table) {
                $table->id();
                $table->string('type');
                $table->string('title')->nullable();
                $table->json('data');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        $form = HrForm::create([
            'type'       => $request->type,
            'title'      => $request->title ?: HrForm::typeLabel($request->type) . ' — ' . now()->format('M d, Y'),
            'data'       => $request->data,
            'created_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'id' => $form->id, 'title' => $form->title]);
    }

    public function destroy($id)
    {
        $form = HrForm::findOrFail($id);
        \App\Models\ActivityLog::log('delete', 'Human Resource', "Deleted saved form '{$form->title}' (Type: {$form->type})", [
            'model_class' => HrForm::class,
            'record_id'   => $form->id,
            'id'         => $form->id,
            'type'       => $form->type,
            'title'      => $form->title,
            'data'       => $form->data,
            'created_by' => $form->created_by,
        ]);
        $form->delete();
        return response()->json(['success' => true]);
    }

    public function index(Request $request)
    {
        $type = $request->input('type');
        $query = HrForm::with('creator')->orderBy('created_at', 'desc');
        if ($type) $query->where('type', $type);
        return response()->json($query->get()->map(fn($f) => [
            'id'         => $f->id,
            'type'       => $f->type,
            'title'      => $f->title,
            'data'       => $f->data,
            'created_by' => $f->creator?->name ?? 'Unknown',
            'created_at' => $f->created_at->format('M d, Y g:i A'),
        ]));
    }
}