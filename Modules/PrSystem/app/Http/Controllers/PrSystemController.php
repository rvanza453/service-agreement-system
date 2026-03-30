<?php

namespace Modules\PrSystem\Http\Controllers;

use Modules\PrSystem\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrSystemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('prsystem::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('prsystem::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('prsystem::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('prsystem::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
