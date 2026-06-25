@if ($user->isJournalist() && $tab === 'publications')
    <div class="account-panel">
        <div class="panel-header-row">
            <h2 class="form-heading"><i class="fa-solid fa-newspaper" aria-hidden="true"></i> Mes publications</h2>
            <a href="{{ route('submit-content.create') }}" class="ci-btn ci-btn--primary ci-btn--sm">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> Déposer un contenu
            </a>
        </div>
        @if ($publications->isEmpty())
            <div class="empty-state-page empty-state-page--inline">
                <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                <p>Aucune soumission pour le moment.</p>
                <a href="{{ route('submit-content.create') }}" class="ci-btn ci-btn--primary">Proposer un contenu</a>
            </div>
        @else
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr><th>Titre</th><th>Statut</th><th>Date</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($publications as $submission)
                            <tr>
                                <td>{{ $submission->title }}</td>
                                <td><span class="status-chip status-chip--{{ $submission->status }}">{{ $submission->statusLabel() }}</span></td>
                                <td>{{ $submission->created_at->format('d/m/Y') }}</td>
                                <td>
                                    @if ($submission->content)
                                        <a href="{{ route('contents.show', $submission->content->slug) }}" class="ci-btn ci-btn--outline ci-btn--sm">Voir</a>
                                    @endif
                                </td>
                            </tr>
                            @if ($submission->status === 'rejected' && $submission->review_note)
                                <tr><td colspan="4" class="review-note"><strong>Motif :</strong> {{ $submission->review_note }}</td></tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@elseif ($user->isJournalist() && $tab === 'sales')
    <div class="account-panel">
        <h2 class="form-heading"><i class="fa-solid fa-chart-line" aria-hidden="true"></i> Mes ventes</h2>
        @if ($sales->isEmpty())
            <div class="empty-state-page empty-state-page--inline">
                <p>Aucune vente enregistrée.</p>
            </div>
        @else
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr><th>Contenu</th><th>Acheteur</th><th>Montant</th><th>Gain</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($sales as $sale)
                            <tr>
                                <td>{{ $sale->content?->title }}</td>
                                <td>{{ $sale->user?->name }}</td>
                                <td>{{ number_format($sale->price, 0) }} €</td>
                                <td>{{ number_format($sale->journalist_earning, 0) }} €</td>
                                <td>{{ $sale->purchased_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@elseif ($user->isJournalist() && $tab === 'wallet')
    <div class="account-panel">
        <h2 class="form-heading"><i class="fa-solid fa-wallet" aria-hidden="true"></i> Mon portefeuille</h2>
        <div class="wallet-balance-card">
            <span>Solde disponible</span>
            <strong>{{ number_format($user->wallet_balance, 0) }} €</strong>
        </div>
        <p class="form-intro">Demandez un reversement (minimum {{ number_format($minPayoutAmount ?? 50, 0) }} €) vers mobile money ou virement bancaire.</p>

        @php $hasPendingPayout = ($payoutRequests ?? collect())->contains(fn ($r) => $r->isPending()); @endphp

        @if (! $hasPendingPayout && $user->wallet_balance >= ($minPayoutAmount ?? 50))
            <form action="{{ route('account.wallet.payout') }}" method="POST" class="wallet-payout-form">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="payout_amount">Montant (€)</label>
                        <input type="number" id="payout_amount" name="amount" step="1" min="{{ (int) ($minPayoutAmount ?? 50) }}" max="{{ (int) $user->wallet_balance }}" value="{{ old('amount', (int) $user->wallet_balance) }}" required>
                        @error('amount')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="payout_method">Mode de reversement</label>
                        <select id="payout_method" name="method" required>
                            <option value="orange_money" @selected(old('method') === 'orange_money')>Orange Money</option>
                            <option value="wave" @selected(old('method') === 'wave')>Wave</option>
                            <option value="mtn" @selected(old('method') === 'mtn')>MTN</option>
                            <option value="bank_transfer" @selected(old('method') === 'bank_transfer')>Virement bancaire</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="payout_phone">Numéro mobile money</label>
                    <input type="text" id="payout_phone" name="payout_phone" value="{{ old('payout_phone', $user->phone) }}" placeholder="620 00 00 00">
                    @error('payout_phone')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="payout_details">Coordonnées bancaires (si virement)</label>
                    <textarea id="payout_details" name="payout_details" rows="3" placeholder="IBAN, banque, titulaire du compte…">{{ old('payout_details') }}</textarea>
                </div>
                <button type="submit" class="ci-btn ci-btn--primary">
                    <i class="fa-solid fa-money-bill-transfer" aria-hidden="true"></i> Demander un reversement
                </button>
            </form>
        @elseif ($hasPendingPayout)
            <p class="ci-alert ci-alert--info">Une demande de reversement est en cours de traitement.</p>
        @else
            <p class="form-intro">Solde insuffisant pour demander un reversement (minimum {{ number_format($minPayoutAmount ?? 50, 0) }} €).</p>
        @endif

        @if (($payoutRequests ?? collect())->isNotEmpty())
            <h3 class="form-subheading">Mes demandes</h3>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead><tr><th>Montant</th><th>Mode</th><th>Statut</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach ($payoutRequests as $payout)
                            <tr>
                                <td>{{ number_format($payout->amount, 0) }} €</td>
                                <td>{{ $paymentMethods[$payout->method]['label'] ?? $payout->method }}</td>
                                <td><span class="status-chip">{{ $payout->statusLabel() }}</span></td>
                                <td>{{ $payout->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($walletTransactions->isEmpty())
            <p>Aucune transaction pour le moment.</p>
        @else
            <h3 class="form-subheading">Historique</h3>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead><tr><th>Description</th><th>Montant</th><th>Solde</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach ($walletTransactions as $tx)
                            <tr>
                                <td>{{ $tx->description }}</td>
                                <td>{{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount, 0) }} €</td>
                                <td>{{ number_format($tx->balance_after, 0) }} €</td>
                                <td>{{ $tx->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@elseif ($user->isJournalist() && $tab === 'received_orders')
    <div class="account-panel">
        <h2 class="form-heading"><i class="fa-solid fa-inbox" aria-hidden="true"></i> Commandes reçues</h2>
        @if ($receivedOrders->isEmpty())
            <p>Aucune commande assignée.</p>
        @else
            @foreach ($receivedOrders as $order)
                <article class="order-card">
                    <h3>{{ $order->title }}</h3>
                    <p>{{ Str::limit($order->description, 200) }}</p>
                    <p class="order-meta"><span class="status-chip">{{ $order->statusLabel() }}</span> · {{ $order->country }}</p>
                </article>
            @endforeach
        @endif
    </div>

@elseif ($tab === 'orders')
    <div class="account-panel">
        <div class="panel-header-row">
            <h2 class="form-heading"><i class="fa-solid fa-box" aria-hidden="true"></i> Mes commandes</h2>
            <a href="{{ route('order-content.create') }}" class="ci-btn ci-btn--primary ci-btn--sm">Nouvelle commande</a>
        </div>
        @if ($contentOrders->isEmpty())
            <div class="empty-state-page empty-state-page--inline">
                <p>Vous n'avez pas encore commandé de sujet.</p>
                <a href="{{ route('order-content.create') }}" class="ci-btn ci-btn--primary">Commander un contenu</a>
            </div>
        @else
            @foreach ($contentOrders as $order)
                <article class="order-card">
                    <h3>{{ $order->title }}</h3>
                    <p>{{ Str::limit($order->description, 200) }}</p>
                    <p class="order-meta">
                        <span class="status-chip">{{ $order->statusLabel() }}</span>
                        @if ($order->budget) · Budget {{ number_format($order->budget, 0) }} € @endif
                        @if ($order->deadline) · Échéance {{ $order->deadline->format('d/m/Y') }} @endif
                    </p>
                </article>
            @endforeach
        @endif
    </div>

@elseif ($tab === 'billing')
    <div class="account-panel">
        <h2 class="form-heading"><i class="fa-solid fa-file-invoice" aria-hidden="true"></i> Facturation</h2>

        @if (isset($activeSubscription) && $activeSubscription)
            <div class="subscription-status-box">
                <h3><i class="fa-solid fa-id-card" aria-hidden="true"></i> Abonnement actif</h3>
                <p>
                    <strong>{{ $activeSubscription->product?->name ?? 'Abonnement' }}</strong>
                    — valide jusqu'au {{ $activeSubscription->ends_at->format('d/m/Y') }}
                    @if ($activeSubscription->product?->discount_percent)
                        <br><span class="form-hint">{{ $activeSubscription->product->discount_percent }} % de réduction sur les contenus payants.</span>
                    @endif
                </p>
                <a href="{{ route('products') }}" class="ci-btn ci-btn--outline ci-btn--sm">Voir les offres</a>
            </div>
        @else
            <p>Aucun abonnement actif. <a href="{{ route('products') }}">Découvrir nos offres</a>.</p>
        @endif

        @if (isset($subscriptions) && $subscriptions->isNotEmpty())
            <h3 class="form-subheading">Historique abonnements</h3>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead><tr><th>Offre</th><th>Montant</th><th>Période</th><th>Statut</th></tr></thead>
                    <tbody>
                        @foreach ($subscriptions as $subscription)
                            <tr>
                                <td>{{ $subscription->product?->name ?? '—' }}</td>
                                <td>{{ number_format($subscription->price_eur, 0) }} €</td>
                                <td>{{ $subscription->starts_at->format('d/m/Y') }} → {{ $subscription->ends_at->format('d/m/Y') }}</td>
                                <td>{{ $subscription->isActive() ? 'Actif' : ucfirst($subscription->status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <h3 class="form-subheading">Achats de contenus</h3>
        @if ($purchases->isEmpty())
            <p>Aucune facture disponible.</p>
        @else
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead><tr><th>Facture</th><th>Contenu</th><th>Montant</th><th>Paiement</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach ($purchases as $purchase)
                            @if ($purchase->content)
                                <tr>
                                    <td>{{ $purchase->invoice_number ?? '—' }}</td>
                                    <td>{{ $purchase->content->title }}</td>
                                    <td>{{ number_format($purchase->price, 0) }} €</td>
                                    <td>{{ $paymentMethods[$purchase->payment_method]['label'] ?? $purchase->payment_method ?? '—' }}</td>
                                    <td>{{ $purchase->purchased_at->format('d/m/Y') }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@elseif ($tab === 'notifications')
    <div class="account-panel">
        <div class="panel-header-row">
            <h2 class="form-heading"><i class="fa-solid fa-bell" aria-hidden="true"></i> Notifications</h2>
            @if ($unreadNotifications > 0)
                <form action="{{ route('account.notifications.read') }}" method="POST">
                    @csrf
                    <button type="submit" class="ci-btn ci-btn--outline ci-btn--sm">Tout marquer comme lu</button>
                </form>
            @endif
        </div>
        @if ($notifications->isEmpty())
            <p>Aucune notification.</p>
        @else
            <div class="notifications-list">
                @foreach ($notifications as $notification)
                    <article @class(['notification-item', 'is-unread' => ! $notification->isRead()])>
                        <h3>{{ $notification->title }}</h3>
                        <p>{{ $notification->message }}</p>
                        <time>{{ $notification->created_at->diffForHumans() }}</time>
                        @if ($notification->action_url)
                            <a href="{{ $notification->action_url }}" class="ci-btn ci-btn--outline ci-btn--sm">Voir</a>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>

@elseif ($tab === 'profile')
    <div class="account-panel">
        <h2 class="form-heading"><i class="fa-solid fa-id-card" aria-hidden="true"></i> Mon profil</h2>

        @if (isset($profileCompletion))
            <div class="profile-completion" role="status">
                <div class="profile-completion__head">
                    <span>Profil complété à {{ $profileCompletion['percent'] }}%</span>
                </div>
                <div class="profile-completion__bar" aria-hidden="true">
                    <span style="width: {{ $profileCompletion['percent'] }}%"></span>
                </div>
                @if ($profileCompletion['missing'] !== [])
                    <p class="form-hint">À compléter : {{ implode(', ', array_slice($profileCompletion['missing'], 0, 4)) }}@if (count($profileCompletion['missing']) > 4)…@endif</p>
                @endif
            </div>
        @endif

        @if ($user->publicProfileUrl())
            <p class="form-intro">Profil public : <a href="{{ $user->publicProfileUrl() }}" target="_blank" rel="noopener">{{ $user->publicProfileUrl() }}</a></p>
        @endif

        <form class="ci-form profile-form" method="POST" action="{{ route('account.profile.update') }}">
            @csrf
            @method('PUT')
            @include('partials.profile-form-fields')
            <button type="submit" class="ci-btn ci-btn--primary">Enregistrer le profil</button>
        </form>
    </div>

@endif
