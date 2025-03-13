<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

final class StoreTeamInvitationRequest extends FormRequest
{
    /**
     * The cached team instance from the route.
     */
    private ?Team $teamInstance = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasTeamPermission($this->team(), 'edit-team') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('team_invitations')->where(fn (\Illuminate\Database\Query\Builder $query) => $query->where('team_id', $this->team()->id)),
            ],
            'role' => ['required', 'string', Rule::in(['admin', 'member'])],
        ];
    }

    /**
     * Get the team instance from the route.
     */
    private function team(): Team
    {
        if (! $this->teamInstance instanceof Team) {
            $team = $this->route('team');

            if (! $team instanceof Team) {
                throw new InvalidArgumentException('Route parameter "team" must be an instance of Team');
            }

            $this->teamInstance = $team;
        }

        return $this->teamInstance;
    }
}
