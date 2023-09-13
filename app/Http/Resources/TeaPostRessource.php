<?php

namespace App\Http\Resources;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeaPostRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->tea_name,
            "image" => $this->tea_image_path,
            "teaType" => $this->tea_type,
            "howToPrepareTea" => $this->how_to_prepare_tea,
            "teaInWaterTime" => $this->tea_in_water_time,
            // "comments"=>Comment::all(),
        ];
    }
}
