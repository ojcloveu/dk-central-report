<?php

namespace App\Http\Resources;

use App\Http\Controllers\Admin\BetReportController;
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
        $betReportController = app(BetReportController::class);

        return [
            'id' => $this->id,
            'account' => $this->account,
            'channel' => $this->channel,
            'trandate' => $this->trandate,
            'master' => $this->master,
            'min' => $this->min,
            'max' => $this->max,
            'count' => $this->count,
            'turnover' => $this->turnover,
            'winlose' => $this->winlose,
            'lp' => [
                'percentage' => $this->lp,
                'color' => $betReportController->getLpColor($this->lp)
            ],
        ];
    }
}
