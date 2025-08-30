<?php
namespace App\Http\Controllers\Web;


use App\Http\Controllers\Controller;


class PosController extends Controller
{
    // public function index()
    // {
    //     return view('pos'); // Vue จะ mount ลงในหน้านี้
    // }
    // public function index()
    // {
    //     return view('pos.index', [
    //         'userRole' => auth('web')->user()->role ?? 'cashier',
    //     ]);
    // }
    public function index()
    {
        $role = auth('web')->user()->role ?? 'cashier';
        return view('pos.index', compact('role'));
    }
}