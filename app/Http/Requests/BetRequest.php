<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account' => 'required|string|max:255',
            'channel' => 'required|string|max:255',
            'trandate' => 'required|date',
            'master' => 'required|string|max:255',
            'min' => 'required|integer|min:0',
            'max' => 'required|integer|min:0',
            'count' => 'required|integer|min:0',
            'turnover' => 'required|numeric|min:0',
            'winlose' => 'required|numeric',
            'lp' => 'required|numeric|min:0|max:100',
            
            // New fields for sync functionality
            'created_by' => 'nullable|exists:users,id',
            'channel_id' => 'nullable|exists:channels,id',
            'bet_amount' => 'nullable|numeric|min:0',
            'win_lose' => 'nullable|integer|in:0,1',
            'payout' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'account' => 'Account',
            'channel' => 'Channel',
            'trandate' => 'Transaction Date',
            'master' => 'Master',
            'min' => 'Minimum',
            'max' => 'Maximum',
            'count' => 'Count',
            'turnover' => 'Turnover',
            'winlose' => 'Win/Lose',
            'lp' => 'LP Percentage',
            'created_by' => 'Created By User',
            'channel_id' => 'Channel ID',
            'bet_amount' => 'Bet Amount',
            'win_lose' => 'Win/Lose Status',
            'payout' => 'Payout Amount',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
