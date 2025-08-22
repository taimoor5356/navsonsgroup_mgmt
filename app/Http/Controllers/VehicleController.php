<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    private $cars = [
        "Toyota" => [
            "Corolla", "Camry", "Yaris", "Hilux", "Land Cruiser", "RAV4", "Fortuner", "Prado"
        ],
        "Honda" => [
            "Civic", "Accord", "City", "CR-V", "HR-V", "Fit", "Pilot", "Jazz"
        ],
        "Suzuki" => [
            "Alto", "Swift", "Wagon R", "Cultus", "Mehran", "Ciaz", "Vitara", "Bolan"
        ],
        "Mercedes" => [
            "A-Class", "C-Class", "E-Class", "S-Class", "GLA", "GLC", "GLE", "GLS"
        ],
        "BMW" => [
            "1 Series", "3 Series", "5 Series", "7 Series", "X1", "X3", "X5", "X7"
        ],
        "Hyundai" => [
            "Elantra", "Sonata", "Tucson", "Santa Fe", "Accent", "Venue", "Palisade", "Creta"
        ],
        "Kia" => [
            "Picanto", "Sportage", "Sorento", "Seltos", "Cerato", "Carnival", "Rio", "Stinger"
        ],
        "Chevrolet" => [
            "Spark", "Cruze", "Malibu", "Camaro", "Equinox", "Traverse", "Tahoe", "Suburban"
        ],
        "Others" => [
            "Nissan Altima", "Nissan Patrol", "Ford Mustang", "Ford F-150",
            "Audi A4", "Audi Q7", "Porsche Cayenne", "Jeep Wrangler"
        ]
    ];
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

    public function brands(Request $request)
    {
        return response()->json(array_keys($this->cars));
    }

    public function models(Request $request)
    {
        $brand = $request->get('brand');
        if (!$brand || !isset($this->cars[$brand])) {
            return response()->json([]);
        }
        return response()->json($this->cars[$brand]);
    }
}
