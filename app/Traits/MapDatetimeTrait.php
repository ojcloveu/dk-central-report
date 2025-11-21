<?php

namespace App\Traits;

use Carbon\Carbon;

trait MapDatetimeTrait
{
  public function getStartDate($key)
  {
    switch ($key) {
      case 'td':
        $startDate = Carbon::today()->toDateString();
        $endDate = Carbon::today()->toDateString();
        break;
      case 'ytd':
        $startDate = Carbon::yesterday()->toDateString();
        $endDate = Carbon::yesterday()->toDateString();
        break;
      case 'tm':
        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->endOfMonth()->toDateString();
        break;
      case '7d':
        $startDate = Carbon::now()->subDays(7)->toDateString();
        $endDate = Carbon::today()->toDateString();
        break;
      case '1m':
        $startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        // $endDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();
        $endDate = Carbon::today()->toDateString();
        break;
      case '3m':
        $startDate = Carbon::now()->subMonths(3)->startOfMonth()->toDateString();
        // $endDate = Carbon::today()->subMonth()->endOfMonth()->toDateString();
        $endDate = Carbon::today()->toDateString();
        break;
      case '6m':
        $startDate = Carbon::now()->subMonths(6)->startOfMonth()->toDateString();
        $endDate = Carbon::today()->toDateString();
        break;
      case 'ty':
        $startDate = Carbon::now()->startOfYear()->toDateString();
        $endDate = Carbon::now()->endOfYear()->toDateString();
        break;
      case 'ly':
        $startDate = Carbon::now()->subYear()->startOfYear()->toDateString();
        $endDate = Carbon::now()->subYear()->endOfYear()->toDateString();
        break;
      case 'all_time':
      default:
        $startDate = Carbon::createFromDate(2000, 1, 1)->toDateString();
        $endDate = Carbon::today()->toDateString();
    }

    return (object) [
      'start_date' => $startDate,
      'end_date' => $endDate,
    ];
  }
}