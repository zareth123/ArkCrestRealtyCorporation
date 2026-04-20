<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Expense;
use App\Models\ExpenseCategory;

class DepartmentController extends Controller
{
    public function admin()
    {
        $department = Department::where('slug', 'admin')->first();
        $categories = $department->categories;
        $expenses = $department->expenses()->orderBy('expense_date', 'desc')->get();
        
        return view('departments.admin', compact('department', 'categories', 'expenses'));
    }

    public function sales()
    {
        $department = Department::where('slug', 'sales')->first();
        $categories = $department->categories;
        $expenses = $department->expenses()->orderBy('expense_date', 'desc')->get();
        
        return view('departments.sales', compact('department', 'categories', 'expenses'));
    }

    public function hr()
    {
        $department = Department::where('slug', 'hr')->first();
        $categories = $department->categories;
        $expenses = $department->expenses()->orderBy('expense_date', 'desc')->get();
        
        return view('departments.hr', compact('department', 'categories', 'expenses'));
    }

    public function finance()
    {
        $department = Department::where('slug', 'finance')->first();
        $categories = $department->categories;
        $expenses = $department->expenses()->orderBy('expense_date', 'desc')->get();
        
        return view('departments.finance', compact('department', 'categories', 'expenses'));
    }

    public function executive()
    {
        $department = Department::where('slug', 'executive')->first();
        $categories = $department->categories;
        $expenses = $department->expenses()->orderBy('expense_date', 'desc')->get();
        
        return view('departments.executive', compact('department', 'categories', 'expenses'));
    }
    
    // API Methods
    public function addDepartment(Request $request)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $name = trim($request->name);
        if (!$name) return response()->json(['success' => false, 'message' => 'Name is required.']);
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $name));
        if (Department::where('slug', $slug)->exists()) {
            return response()->json(['success' => false, 'message' => 'Department already exists.']);
        }
        Department::create(['name' => $name, 'slug' => $slug, 'allowable_budget' => 0]);
        return response()->json(['success' => true]);
    }

    public function updateBudget(Request $request, $id)
    {
        $department = Department::findOrFail($id);
        $department->allowable_budget = $request->allowable_budget ?? $request->budget;
        $department->budget_from = $request->budget_from ?: null;
        $department->budget_to = $request->budget_to ?: null;
        $department->save();
        
        return response()->json([
            'success' => true,
            'budget' => $department->allowable_budget,
            'budget_from' => $department->budget_from?->format('Y-m-d'),
            'budget_to' => $department->budget_to?->format('Y-m-d'),
        ]);
    }
    
    public function addCategory(Request $request, $id)
    {
        // Check if category already exists (case-insensitive)
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
        // Check if category already exists (case-insensitive)
        $existingCategory = ExpenseCategory::where('department_id', $id)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->first();
        
        // If category doesn't exist, create it
        if (!$existingCategory) {
            $category = ExpenseCategory::create([
                'department_id' => $id,
                'name' => $request->name
            ]);
        }
        
        // Check if expense row exists for this date
        $expense = Expense::where('department_id', $id)
            ->where('expense_date', $request->date)
            ->first();
        
        if ($expense) {
            // Update existing row - add/update category amount
            $categoriesData = $expense->categories_data;
            $categoriesData[$request->name] = $request->amount;
            
            // Recalculate total
            $total = array_sum($categoriesData);
            
            $expense->categories_data = $categoriesData;
            $expense->total_amount = $total;
            $expense->save();
        } else {
            // Create new row with this category
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
        
        // Remove this category from all expenses in this department
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
        
        // Delete the category
        $category->delete();
        
        return response()->json(['success' => true]);
    }
}
