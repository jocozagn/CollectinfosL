@php
    /** @var \App\Services\CurrencyService $currency */
    $currency = $currency ?? app(\App\Services\CurrencyService::class);
    $layout = $layout ?? 'inline';

    if (isset($content) && $content instanceof \App\Models\Content) {
        $dual = $currency->dualForContent($content);
    } else {
        $amount = (float) ($amount ?? 0);
        $dual = $currency->dual($amount, isset($gnfAmount) ? (int) $gnfAmount : null);
    }

    $gnfPrefix = ($dual['gnf_manual'] ?? false) ? '' : '≈ ';
@endphp

@if ($layout === 'stack')
    <span class="price-dual price-dual--stack">
        <span class="price-dual__eur">{{ $dual['eur'] }}</span>
        @if ($showGnf ?? true)
            <span class="price-dual__gnf">{{ $gnfPrefix }}{{ $dual['gnf'] }}</span>
        @endif
    </span>
@else
    <span class="price-dual">
        <span class="price-dual__eur">{{ $dual['eur'] }}</span>
        @if ($showGnf ?? true)
            <span class="price-dual__gnf">{{ $gnfPrefix }}{{ $dual['gnf'] }}</span>
        @endif
    </span>
@endif
