<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BetReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account' => $this->account,
            'channel' => $this->channel,
            'trandate' => $this->trandate,
            'master' => $this->master,
            'min' => $this->min,
            'max' => $this->max,
            'count' => $this->formatCount($this->count),
            'turnover' => $this->formatAmount($this->turnover),
            'winlose' => $this->formatAmount($this->winlose),
            'lp' => [
                'percentage' => $this->formatPercentageZeroDecimal($this->lp),
                'color' => $this->lpColor,
            ],
        ];
    }
}
