<?php

namespace App\Traits\Utils;

use Illuminate\Support\Str;

trait ResourceNumberFormat
{
    public function rnfNumberFormat(
        string $key,
        int|float|string|null $amount,
        $decimal = 2,
        $left = '',
        $right = ''
    ) {
        $amount = $this->rnfRemoveNumber($amount, $decimal);

        $formattedAmount = $amount < 0
            ? "-{$left}" . number_format(abs($amount), $decimal) . $right
            : $left . number_format($amount, $decimal) . $right;

        return [
            $key => $amount,
            $key . '_format' => $formattedAmount
        ];
    }

    public function rnfNumberFormatRemoveMinus(
        string $key,
        int|float|string|null $amount,
        $decimal = 2,
        $left = '',
        $right = ''
    ) {
        $data = $this->rnfNumberFormat($key, $amount, $decimal, $left, $right);
        $data[$key . '_format'] = Str::replace('-', '', $data[$key . '_format']);
        return $data;
    }

    public function rnfRemoveNumber($amount, $decimal = 2)
    {
        // Convert the amount to a string and split it into integer and decimal parts
        $amountStr = (string) $amount;
        $parts = explode('.', $amountStr);

        // Ensure there is a decimal part to work with
        if (count($parts) > 1) {
            // Truncate the decimal part to the specified number of decimal places
            $parts[1] = substr($parts[1], 0, $decimal);

            // Join the integer and truncated decimal parts
            $amountStr = implode('.', $parts);
        }

        // Convert the string back to a float for numerical operations
        return (float) $amountStr;
    }
    public function formatAmount($amount)
    {
        return $amount < 0
            ? '-$' . number_format(abs($this->rnfRemoveNumber($amount)), 0)
            : '$' . number_format($this->rnfRemoveNumber($amount), 0);
    }

    /**
     * Formats an integer value with thousands separators and no decimals
     * e.g., 1000 -> 1,000
     */
    public function formatCount(int|float|string|null $count): string
    {
        $number = $this->rnfRemoveNumber($count, 0);
        $formattedCount = number_format($number, 0);
        return $number < 0
            ? '-' . $formattedCount
            : $formattedCount;
    }

    /**
     * Formats a percentage value (assumed to be already multiplied by 100)
     * to zero decimal places with a '%' sign, using truncation (floor)
     * e.g., 79.2786 -> 79%
     */
    public function formatPercentageZeroDecimal(int|float|string|null $percentageAmount): string
    {
        $number = (float) $percentageAmount;
        $truncatedNumber = floor(abs($number));
        $formattedNumber = number_format($truncatedNumber, 0);

        $result = $number < 0
            ? '-' . $formattedNumber
            : $formattedNumber;

        return $result;
    }
}
