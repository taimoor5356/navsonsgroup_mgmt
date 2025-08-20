<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        //
    }

    public function searchVehicleByNumber(Request $request)
    {
        if(!empty($request->registration_number)){
            $vehicleExists = Vehicle::with('user')->where('registration_number', strtolower($request->registration_number))->first();
            if (isset($vehicleExists)) {
                $data = [
                    'status' => true,
                    'data' => $vehicleExists
                ];
            } else {
                $data = [
                    'status' => false,
                    'data' => []
                ];
            }
            return $data;
        }
    }
}
