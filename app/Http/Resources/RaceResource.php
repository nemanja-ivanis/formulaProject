<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'season' => $this->season,
            'driver_position' => $this->driver_position,
            'driver_name' => $this->driver_name,
            'car_constructor' => $this->car_constructor,
            'time' => $this->time,
            'status' => $this->status,
            'driver_number' => $this->driver_number,
            'race_name' => $this->race_name,
            'race_datetime' => $this->race_datetime
        ];
    }
}
