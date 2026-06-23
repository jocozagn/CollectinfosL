<article class="product-card" data-slug="{{ $item['slug'] }}">
    <div class="product-thumb">
        <div class="product-badges">
            <span class="badge badge-type badge-type-{{ $item['type'] ?? 'article' }}">
                {{ $item['type_label'] ?? 'Article' }}
            </span>
            @if (! empty($item['is_free']))
                <span class="badge badge-free"><i class="fa-solid fa-gift" aria-hidden="true"></i> Gratuit</span>
            @elseif (! empty($item['is_paid']))
                <span class="badge badge-paid"><i class="fa-solid fa-tag" aria-hidden="true"></i> {{ number_format($item['price'], 0) }} €</span>
            @elseif (($item['access'] ?? 'free') === 'subscriber')
                <span class="badge badge-subscriber"><i class="fa-solid fa-lock" aria-hidden="true"></i> Abonnés</span>
            @endif
        </div>

        <a href="{{ route('contents.show', $item['slug']) }}">
            <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy">
        </a>

        <div class="product-actions">
            @if (! empty($item['preview_enabled']))
                <button
                    type="button"
                    class="action-btn action-preview"
                    title="Aperçu"
                    data-preview-title="{{ e($item['title']) }}"
                    data-preview-mode="{{ $item['preview_mode'] ?? 'text' }}"
                    data-preview-seconds="{{ $item['preview_seconds'] ?? 15 }}"
                    data-preview-url="{{ $item['preview_url'] ?? '' }}"
                    data-preview-embed="{{ $item['preview_embed'] ?? '' }}"
                    data-preview-text="{{ e(str_replace(["\r", "\n"], ' ', $item['preview_text'] ?? '')) }}"
                >
                    <i class="fa-solid fa-circle-play" aria-hidden="true"></i> Aperçu
                </button>
            @endif

            @if (($item['action'] ?? 'read') === 'cart')
                <form action="{{ route('cart.add', $item['slug']) }}" method="POST" class="cart-add-form">
                    @csrf
                    <button type="submit" class="action-btn" title="Ajouter au panier">
                        <i class="fa-solid fa-cart-plus" aria-hidden="true"></i> Panier
                    </button>
                </form>
            @else
                <a href="{{ route('contents.show', $item['slug']) }}" class="action-btn">
                    <i class="fa-solid fa-book-open" aria-hidden="true"></i> Lire
                </a>
            @endif

            <button type="button" class="action-btn outline icon-only" title="Liste de souhaits" aria-label="Liste de souhaits">
                <i class="fa-regular fa-heart" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <div class="product-info">
        <h3>
            <a href="{{ route('contents.show', $item['slug']) }}">{{ $item['title'] }}</a>
        </h3>
        @if (! empty($item['is_paid']) && empty($item['is_free']))
            <div class="product-price">{{ number_format($item['price'], 0) }} €</div>
        @elseif (! empty($item['is_free']))
            <div class="product-access-label product-access-free">
                <i class="fa-solid fa-unlock" aria-hidden="true"></i> Accès libre
            </div>
        @endif
    </div>
</article>
