<?php

namespace App\Http\Controllers;

use App\Models\ServiceAddonRate;
use App\Models\ServiceCategoryRate;
use App\Models\ServiceType;
use App\Models\VehicleCategory;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $serviceTypes = ServiceType::orderBy('name')->get();
        $categories = VehicleCategory::orderBy('id')->get();

        // Keep the matrix dense: every (service type x category) pair should have a row.
        foreach ($serviceTypes as $serviceType) {
            foreach ($categories as $category) {
                ServiceCategoryRate::firstOrCreate([
                    'service_type_id' => $serviceType->id,
                    'vehicle_category_id' => $category->id,
                ], ['price' => 0]);
            }
        }

        $rates = ServiceCategoryRate::all()->groupBy('service_type_id')->map(function ($rows) {
            return $rows->keyBy('vehicle_category_id');
        });

        $data['header_title'] = 'Service Rates';
        $data['serviceTypes'] = $serviceTypes;
        $data['categories'] = $categories;
        $data['rates'] = $rates;
        $data['addonRates'] = ServiceAddonRate::orderBy('label')->get();
        return view('admin.services.types.index', $data);
    }

    /**
     * Update all category prices for one service type row, or one addon rate, via AJAX.
     */
    public function updatePrice(Request $request)
    {
        $request->validate([
            'model' => 'required|in:service_category_rates,addon',
        ]);

        if ($request->model === 'addon') {
            $request->validate([
                'id' => 'required|integer',
                'price' => 'required|numeric|min:0',
            ]);

            $record = ServiceAddonRate::find($request->id);
            if (!$record) {
                return response()->json(['status' => false, 'message' => 'Record not found'], 404);
            }

            $record->price = $request->price;
            $record->save();

            return response()->json(['status' => true]);
        }

        $request->validate([
            'service_type_id' => 'required|integer|exists:service_types,id',
            'prices' => 'required|array|min:1',
            'prices.*' => 'required|numeric|min:0',
        ]);

        foreach ($request->prices as $categoryId => $price) {
            ServiceCategoryRate::updateOrCreate(
                ['service_type_id' => $request->service_type_id, 'vehicle_category_id' => $categoryId],
                ['price' => $price]
            );
        }

        return response()->json(['status' => true]);
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
    public function show(ServiceType $serviceType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceType $serviceType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceType $serviceType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceType $serviceType)
    {
        //
    }
}
