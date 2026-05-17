<?php

namespace Webkul\Order\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Webhook Receive Request
 *
 * Validation rules for incoming webhook payloads.
 * Minimal validation as each platform has different structures.
 */
class WebhookReceiveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Public endpoint - authorization handled via HMAC signature
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Minimal validation - structure varies by platform
            'event' => 'sometimes|string|max:255',
            'data'  => 'sometimes|array',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @return void
     *
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        // Log validation failures for debugging
        Log::warning('Webhook validation failed', [
            'channel' => $this->route('channelCode'),
            'errors'  => $validator->errors()->toArray(),
            'payload' => $this->all(),
        ]);

        parent::failedValidation($validator);
    }
}
