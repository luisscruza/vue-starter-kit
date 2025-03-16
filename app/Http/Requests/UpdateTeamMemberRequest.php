<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateTeamMemberRequest extends FormRequest
{
    /**
     * The team instance from the route.
     */
    private Team $teamInstance;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Team $team */
        $team = $this->route('team');

        $this->teamInstance = $team;

        $user = $this->user();

        return $user?->hasTeamPermission($this->teamInstance, 'edit-team') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role_id' => ['required', 'exists:roles,id,team_id,'.$this->teamInstance->id],
        ];
    }
}
