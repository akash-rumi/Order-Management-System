<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductImportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'rows_total' => $this->rows_total,
            'rows_processed' => $this->rows_processed,
            'error_log' => $this->error_log,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'uploader' => $this->whenLoaded('uploader', function(){
                return [
                    'id' => $this->uploader->id,
                    'name' => $this->uploader->name,
                    'email' => $this->uploader->email,
                ];
            }),
        ];
    }
}
