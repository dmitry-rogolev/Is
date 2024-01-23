<?php

namespace dmitryrogolev\Is\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
	public function toArray(): array 
	{
		return [
			"id" => $this->id, 
			"name"=> $this->name,
			"slug"=> $this->slug,
			"description"=> $this->description,
			"level" => $this->level, 
			"createdAt" => $this->created_at,
			"updatedAt" => $this->updated_at,
			"deletedAt" => $this->when(config("is.uses.soft_deletes"), $this->deleted_at), 
		];
	}
}
