<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthenticateUserResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the authenticated user into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_name' => $this->user_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'photo' => $this->photo ? asset($this->photo) : null,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'identity_type' => $this->identity_type,
            'country' => $this->country,
            'district_id' => $this->district_id,
            'counter_id' => $this->counter_id,
            'role' => $this->role,
            'type' => $this->type,
            'access_type' => $this->access_type,
            'account_type' => $this->account_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
