<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BetReportPeriodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'account' => $this->account,
            'total_count' => $this->formatCount($this->total_count),
            'total_turnover' => $this->formatAmount($this->total_turnover),
            'total_winlose' => $this->formatAmount($this->total_winlose),
            'total_lp' => [
                'percentage' => $this->formatPercentageZeroDecimal($this->total_lp),
                'color' => $this->lpColor,
            ],
        ];
    }
}
