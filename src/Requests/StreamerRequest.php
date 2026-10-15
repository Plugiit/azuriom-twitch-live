<?php

namespace Azuriom\Plugin\TwitchLive\Requests;

use Azuriom\Http\Requests\Traits\ConvertCheckbox;
use Azuriom\Models\User;
use Azuriom\Plugin\TwitchLive\Models\Streamer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StreamerRequest extends FormRequest
{
    use ConvertCheckbox;

    /**
     * The attributes represented by checkboxes.
     *
     * @var array<int, string>
     */
    protected array $checkboxes = [
        'is_featured',
    ];

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeCheckboxes();

        $this->merge(['login' => strtolower(trim((string) $this->input('login')))]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $streamer = $this->route('streamer');

        return [
            'login' => [
                'required', 'string', 'regex:'.Streamer::LOGIN_REGEX,
                Rule::unique('twitch_live_streamers', 'login')->ignore($streamer?->id),
            ],
            'user_name' => ['nullable', 'string', 'max:50', 'exists:users,name'],
            'is_featured' => ['boolean'],
            'status' => ['required', Rule::in(Streamer::STATUSES)],
        ];
    }

    /**
     * Get the attributes to save on the streamer.
     */
    public function streamerData(): array
    {
        $validated = $this->validated();
        $userName = $validated['user_name'] ?? null;

        return [
            'login' => $validated['login'],
            'user_id' => $userName !== null ? User::where('name', $userName)->value('id') : null,
            'is_featured' => $validated['is_featured'],
            'status' => $validated['status'],
        ];
    }

    public function attributes(): array
    {
        return [
            'login' => trans('twitch-live::messages.fields.login'),
            'user_name' => trans('twitch-live::admin.streamers.member'),
        ];
    }
}
