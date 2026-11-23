<?php

namespace App\Http\Controllers;

use App\Http\Resources\SpotTypeResource;
use App\Models\SpotType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SpotTypeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return SpotTypeResource::collection(
            SpotType::query()->orderBy('name')->get()
        );
    }

    public function show(SpotType $spotType): SpotTypeResource
    {
        return new SpotTypeResource($spotType);
    }
}
