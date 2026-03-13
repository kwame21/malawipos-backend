<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        return response()->json(
            Expense::with('user')
                ->orderBy('date', 'desc')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'amount'   => 'required|numeric',
            'note'     => 'nullable|string',
            'date'     => 'required|date',
        ]);

        $expense = Expense::create([
            'user_id'  => $request->user()->id,
            'category' => $request->category,
            'amount'   => $request->amount,
            'note'     => $request->note,
            'date'     => $request->date,
        ]);

        return response()->json($expense, 201);
    }

    public function show(Expense $expense)
    {
        return response()->json($expense);
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'category' => 'required|string',
            'amount'   => 'required|numeric',
            'note'     => 'nullable|string',
            'date'     => 'required|date',
        ]);

        $expense->update($request->all());
        return response()->json($expense);
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return response()->json(['message' => 'Expense deleted']);
    }
}