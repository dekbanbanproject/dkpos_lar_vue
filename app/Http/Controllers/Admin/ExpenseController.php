<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $q = Expense::query();
        if ($request->filled('from')) $q->whereDate('spent_at', '>=', $request->date('from'));
        if ($request->filled('to'))   $q->whereDate('spent_at', '<=', $request->date('to'));
        if ($request->filled('category')) $q->where('category', $request->category);

        $expenses = $q->latest('spent_at')->paginate(20)->withQueryString();
        $sum = (clone $q)->sum('amount');

        return view('admin.expenses.index', compact('expenses','sum'));
    }

    public function create()
    {
        $ref = 'EX'.now()->format('YmdHis').'-'.rand(100,999);
        return view('admin.expenses.create', compact('ref'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ref_no'   => 'required|string|max:50|unique:expenses,ref_no',
            'title'    => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'amount'   => 'required|numeric|min:0',
            'spent_at' => 'required|date',
            'note'     => 'nullable|string|max:2000',
        ]);

        $data['user_id'] = auth('web')->id();
        Expense::create($data);

        return redirect()->route('admin.expenses.index')->with('success','บันทึกรายจ่ายเรียบร้อย');
    }
}
