    <?php

if (!function_exists('calculate_diskon_total')) {
    /**
     * Hitung diskon berdasarkan total pembelian
     *
     * @param float $totalHarga
     * @return array{amount:int, rate:float}
     */
    function calculate_diskon_total(float $totalHarga): array
    {
        if ($totalHarga >= 50000000) {
            $rate = 0.15;
        } elseif ($totalHarga >= 30000000) {
            $rate = 0.10;
        } elseif ($totalHarga >= 10000000) {
            $rate = 0.05;
        } else {
            $rate = 0;
        }

        $amount = (int) round($totalHarga * $rate);

        return [
            'amount' => $amount,
            'rate'   => $rate,
        ];
    }
}

