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

    public function update(Request $request, $id)
    {
        $request->validate([
            'type'  => 'required|in:dayoff,absences,voucher',
            'title' => 'nullable|string|max:255',
            'data'  => 'required|array',
        ]);

        $form = HrForm::findOrFail($id);

        $form->fill([
            'type'  => $request->type,
            'title' => $request->title ?: $form->title,
            'data'  => $request->data,
        ]);

        // isDirty() is what makes "date modified" accurate: if none of the
        // filled attributes actually differ from what's already stored,
        // save() below won't issue an UPDATE and won't touch updated_at.
        $wasChanged = $form->isDirty();

        $form->save();

        if ($wasChanged) {
            \App\Models\ActivityLog::log('update', 'Human Resource', "Edited saved form '{$form->title}' (Type: {$form->type})", [
                'model_class' => HrForm::class,
                'record_id'   => $form->id,
                'id'          => $form->id,
                'type'        => $form->type,
                'title'       => $form->title,
                'data'        => $form->data,
                'created_by'  => $form->created_by,
            ]);
        }

        return response()->json([
            'success'    => true,
            'id'         => $form->id,
            'title'      => $form->title,
            'changed'    => $wasChanged,
            'updated_at' => $form->updated_at?->format('M d, Y g:i A'),
        ]);
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
            // Only meaningfully different from created_at once the record has actually
            // been edited (see update() below) — the front-end's "Date Modified" sort
            // relies on that distinction.
            'updated_at' => $f->updated_at?->format('M d, Y g:i A'),
        ]));
    }
}