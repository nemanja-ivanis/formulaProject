<?php

namespace App\Http\Controllers;

use App\Http\Resources\RaceResource;
use App\Race;
use Illuminate\Http\Request;

class RacesController extends Controller
{

    protected $race;

    public function __construct(Race $race)
    {
        $this->race = $race;
    }

    public function getRacesTable(Request $request){

        $query = $this->race->orderBy($request->column, $request->order);
        $races = $query->paginate($request->per_page);

        return RaceResource::collection($races);

    }
}
