<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function cashflow(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay()
                                         : now()->startOfMonth();
        $to   = $request->filled('to')   ? Carbon::parse($request->input('to'))->endOfDay()
                                         : now()->endOfDay();

        // รายรับจากออเดอร์ (ยอดสุทธิ)
        $incomeTotal = Order::whereBetween('created_at', [$from, $to])->sum('total');
        $incomeByDay = Order::selectRaw('DATE(created_at) d, SUM(total) s')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('d')->orderBy('d')->pluck('s','d');

        // รายจ่าย
        $expenseTotal = Expense::whereBetween('spent_at', [$from, $to])->sum('amount');
        $expenseByDay = Expense::selectRaw('spent_at d, SUM(amount) s')
            ->whereBetween('spent_at', [$from, $to])
            ->groupBy('d')->orderBy('d')->pluck('s','d');

        // รวมรายวัน
        $days = new Collection();
        for ($d = $from->copy(); $d <= $to; $d->addDay()) {
            $key = $d->toDateString();
            $income  = (float) ($incomeByDay[$key] ?? 0);
            $expense = (float) ($expenseByDay[$key] ?? 0);
            $days->push([
                'date'    => $key,
                'income'  => $income,
                'expense' => $expense,
                'net'     => $income - $expense,
            ]);
        }

        return view('admin.reports.cashflow', [
            'from' => $from->toDateString(),
            'to'   => $to->toDateString(),
            'incomeTotal'  => $incomeTotal,
            'expenseTotal' => $expenseTotal,
            'netTotal'     => $incomeTotal - $expenseTotal,
            'rows'         => $days,
        ]);
    }

    // Export CSV อย่างง่าย
    public function cashflowCsv(Request $request)
    {
        $request->merge(['export' => 1]);
        $resp = $this->cashflow($request)->getData();
        $csv = "date,income,expense,net\n";
        foreach ($resp->rows as $r) $csv .= "{$r['date']},{$r['income']},{$r['expense']},{$r['net']}\n";
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=cashflow.csv'
        ]);
    }
}
