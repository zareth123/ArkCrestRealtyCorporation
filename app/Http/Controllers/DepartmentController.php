<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\DepartmentalExpense;

class DepartmentController extends Controller
{
    /**
     * Sum of budget-request-form amounts currently committed against a
     * department's budget:
     *  - "pending"    => requested_amount for records still PENDING
     *                    (awaiting release) — money earmarked but not
     *                    yet spent.
     *  - "liquidated" => total_expenses for records that are fully
     *                    LIQUIDATED — money actually spent.
     * Matched by the department's name, which is the same string stored
     * on DepartmentalExpense::department (see the Budget Request Form's
     * department <select>, which submits $dept->name).
     */
    private function budgetCommitments(string $departmentName): array
    {
        $pending = (float) DepartmentalExpense::where('department', $departmentName)
            ->where('status', 'PENDING')
            ->sum('requested_amount');

        $liquidated = (float) DepartmentalExpense::where('department', $departmentName)
            ->where('status', 'LIQUIDATED')
            ->sum('total_expenses');

        return ['pending' => $pending, 'liquidated' => $liquidated];
    }

    public function admin()
    {
        $department = Department::where('slug', 'admin')->first();
        $categories = $department->categories;
        $expenses = $department->expenses()->orderBy('expense_date', 'desc')->get();
        $commitments = $this->budgetCommitments($department->name);

        return view('departments.admin', compact('department', 'categories', 'expenses', 'commitments'));
    }

    public function sales()
    {
        $department = Department::where('slug', 'sales')->first();
        $categories = $department->categories;
        $expenses = $department->expenses()->orderBy('expense_date', 'desc')->get();
        $commitments = $this->budgetCommitments($department->name);

        return view('departments.sales', compact('department', 'categories', 'expenses', 'commitments'));
    }

    public function hr()
    {
        $department = Department::where('slug', 'hr')->first();
        $categories = $department->categories;
        $expenses = $department->expenses()->orderBy('expense_date', 'desc')->get();
        $commitments = $this->budgetCommitments($department->name);

        return view('departments.hr', compact('department', 'categories', 'expenses', 'commitments'));
    }

    public function finance()
    {
        $department = Department::where('slug', 'finance')->first();
        if (!$department) {
            $department = Department::create(['name' => 'Finance', 'slug' => 'finance', 'allowable_budget' => 0]);
            foreach (['Retention Fees', 'Penalty/ Fines', 'Tax and Licenses', 'Miscellaneous'] as $cat) {
                ExpenseCategory::firstOrCreate(['department_id' => $department->id, 'name' => $cat]);
            }
        }
        $categories = $department->categories;
        $expenses = $department->expenses()->orderBy('expense_date', 'desc')->get();
        $commitments = $this->budgetCommitments($department->name);

        return view('departments.finance', compact('department', 'categories', 'expenses', 'commitments'));
    }

    public function executive()
    {
        $department = Department::where('slug', 'executive')->first();
        $categories = $department->categories;
        $expenses = $department->expenses()->orderBy('expense_date', 'desc')->get();
        $commitments = $this->budgetCommitments($department->name);

        return view('departments.executive', compact('department', 'categories', 'expenses', 'commitments'));
    }

    public function deleteDepartment($id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $dept = Department::findOrFail($id);
        $dept->delete();
        return response()->json(['success' => true]);
    }

    public function addDepartment(Request $request)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $name = trim($request->name);
        if (!$name) return response()->json(['success' => false, 'message' => 'Name is required.']);
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $name));
        if (Department::where('slug', $slug)->exists()) {
            return response()->json(['success' => false, 'message' => 'Department already exists.']);
        }
        $dept = Department::create(['name' => $name, 'slug' => $slug, 'allowable_budget' => 0]);
        
        if ($request->categories && is_array($request->categories)) {
            foreach ($request->categories as $catName) {
                if (trim($catName)) {
                    ExpenseCategory::create(['department_id' => $dept->id, 'name' => trim($catName)]);
                }
            }
        }
        
        return response()->json(['success' => true]);
    }

    public function updateBudget(Request $request, $id)
    {
        $department = Department::findOrFail($id);
        $department->allowable_budget = $request->allowable_budget ?? $request->budget;
        $department->budget_from = $request->budget_from ?: null;
        $department->budget_to = $request->budget_to ?: null;
        $department->save();

        if ($request->has('categories') && is_array($request->categories)) {
            foreach ($request->categories as $cat) {
                if (trim($cat)) {
                    ExpenseCategory::firstOrCreate(
                        ['department_id' => $id, 'name' => trim($cat)]
                    );
                }
            }
            ExpenseCategory::where('department_id', $id)
                ->whereNotIn('name', array_filter(array_map('trim', $request->categories)))
                ->delete();
        }
        
        return response()->json([
            'success' => true,
            'budget' => $department->allowable_budget,
            'budget_from' => $department->budget_from?->format('Y-m-d'),
            'budget_to' => $department->budget_to?->format('Y-m-d'),
        ]);
    }
    
    public function addCategory(Request $request, $id)
    {
        $exists = ExpenseCategory::where('department_id', $id)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->exists();
        
        if ($exists) {
            return response()->json([
                'success' => false, 
                'message' => 'Category already exists!'
            ], 400);
        }
        
        $category = ExpenseCategory::create([
            'department_id' => $id,
            'name' => $request->name
        ]);
        
        return response()->json(['success' => true, 'category' => $category]);
    }
    
    public function addCategoryWithAmount(Request $request, $id)
    {
        $existingCategory = ExpenseCategory::where('department_id', $id)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->first();

        if (!$existingCategory) {
            $category = ExpenseCategory::create([
                'department_id' => $id,
                'name' => $request->name
            ]);
        }

        $expense = Expense::where('department_id', $id)
            ->where('expense_date', $request->date)
            ->first();
        
        if ($expense) {
            $categoriesData = $expense->categories_data;
            $categoriesData[$request->name] = $request->amount;
            
            $total = array_sum($categoriesData);
            
            $expense->categories_data = $categoriesData;
            $expense->total_amount = $total;
            $expense->save();
        } else {
            $categoriesData = [$request->name => $request->amount];
            
            $expense = Expense::create([
                'department_id' => $id,
                'expense_date' => $request->date,
                'categories_data' => $categoriesData,
                'total_amount' => $request->amount
            ]);
        }
        
        return response()->json(['success' => true, 'expense' => $expense]);
    }
    
    public function addExpense(Request $request, $id)
    {
        $expense = Expense::create([
            'department_id' => $id,
            'expense_date' => $request->date,
            'categories_data' => $request->categories,
            'total_amount' => $request->total
        ]);
        
        return response()->json(['success' => true, 'expense' => $expense]);
    }
    
    public function updateExpense(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        $expense->categories_data = $request->categories;
        $expense->total_amount = $request->total;
        $expense->save();
        
        return response()->json(['success' => true, 'expense' => $expense]);
    }
    
    public function deleteExpense($id)
    {
        Expense::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
    
    public function deleteCategory($id)
    {
        $category = ExpenseCategory::findOrFail($id);
        $categoryName = $category->name;
        $departmentId = $category->department_id;
        
        $expenses = Expense::where('department_id', $departmentId)->get();
        foreach ($expenses as $expense) {
            $categoriesData = $expense->categories_data;
            if (isset($categoriesData[$categoryName])) {
                unset($categoriesData[$categoryName]);
                $expense->categories_data = $categoriesData;
                $expense->total_amount = array_sum($categoriesData);
                $expense->save();
            }
        }

        $category->delete();
        
        return response()->json(['success' => true]);
    }
}