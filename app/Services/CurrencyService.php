<?php



namespace App\Services;



use App\Models\Content;



class CurrencyService

{

    public function catalogCurrency(): string

    {

        return (string) config('collectinfos.currency.catalog', 'EUR');

    }



    public function paymentCurrency(): string

    {

        return (string) config('collectinfos.currency.payment', 'GNF');

    }



    public function eurToGnfRate(): float

    {

        return (float) config('collectinfos.currency.eur_to_gnf', 10000);

    }



    public function eurToGnf(float $eur): int

    {

        return (int) round($eur * $this->eurToGnfRate());

    }



    public function gnfForContent(Content $content): int

    {

        if ($content->hasManualGnfPrice()) {

            return (int) $content->price_gnf;

        }



        return $this->eurToGnf((float) $content->price);

    }



    /** @param \Illuminate\Support\Collection<int, Content> $items */

    public function cartTotalGnf($items, ?\App\Models\User $user = null): int

    {

        return (int) $items->sum(fn (Content $content) => app(SubscriptionService::class)->effectiveGnfPrice($content, $user));

    }



    public function gnfToEur(int $gnf): float

    {

        $rate = $this->eurToGnfRate();



        if ($rate <= 0) {

            return 0.0;

        }



        return round($gnf / $rate, 2);

    }



    public function formatEur(float $amount): string

    {

        return number_format($amount, 0, ',', ' ').' €';

    }



    public function formatGnf(int $amount): string

    {

        return number_format($amount, 0, ',', ' ').' GNF';

    }



    /** @return array{eur: string, gnf: string, gnf_raw: int, gnf_manual: bool} */

    public function dual(float $eurAmount, ?int $manualGnf = null): array

    {

        $gnf = $manualGnf !== null && $manualGnf > 0

            ? $manualGnf

            : $this->eurToGnf($eurAmount);



        return [

            'eur' => $this->formatEur($eurAmount),

            'gnf' => $this->formatGnf($gnf),

            'gnf_raw' => $gnf,

            'gnf_manual' => $manualGnf !== null && $manualGnf > 0,

        ];

    }



    /** @return array{eur: string, gnf: string, gnf_raw: int, gnf_manual: bool} */

    public function dualForContent(Content $content): array

    {

        return $this->dual(

            (float) $content->price,

            $content->hasManualGnfPrice() ? (int) $content->price_gnf : null

        );

    }

}


