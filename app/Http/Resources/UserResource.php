<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *       schema="UserResource",
 *       title="User Response",
 *       description="User response",
 * )
 */
class UserResource extends JsonResource
{
    /**
     * @OA\Property(property = "id", type = "integer", example = 1),
     * @OA\Property(property = "name", type = "string", example = "John Doe"),
     * @OA\Property(property = "email", type = "string", example = "T3hYq@example.com"),
     * @OA\Property(property = "cpf", type = "string", example = "000.000.000-00"),
     * @OA\Property(property = "phone", type = "string", example = "(00) 00000-0000"),
     * @OA\Property(property = "created_at", type = "string", example = "2023-01-01 00:00:00"),
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'cpf' => $this->cpf,
            'phone' => $this->phone_formatted,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
