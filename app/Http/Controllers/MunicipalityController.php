<?php

namespace App\Http\Controllers;

use App\Enums\Island;
use App\Http\Resources\MunicipalityResource;
use App\Models\Municipality;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;

class MunicipalityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'island' => ['sometimes', 'string', Rule::enum(Island::class)],
        ]);

        $municipalities = Municipality::query()
            ->when(
                $request->enum('island', Island::class),
                fn ($query, Island $island) => $query->where('island', $island),
            )
            ->orderBy('name')
            ->get();

        return MunicipalityResource::collection($municipalities);
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
    public function show(Municipality $municipality): MunicipalityResource
    {
        return new MunicipalityResource($municipality);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
