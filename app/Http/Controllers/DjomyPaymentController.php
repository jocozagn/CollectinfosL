<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Services\Djomy\DjomyClient;
use App\Services\Djomy\DjomyPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DjomyPaymentController extends Controller
{
    public function __construct(
        private DjomyPaymentService $djomyPayments,
        private DjomyClient $client,
    ) {}

    public function return(string $reference): RedirectResponse
    {
        $transaction = PaymentTransaction::query()
            ->where('reference', $reference)
            ->firstOrFail();

        if ($this->djomyPayments->fulfillIfPaid($transaction)) {
            if ($transaction->isSubscription()) {
                return redirect()
                    ->route('account', ['tab' => 'billing'])
                    ->with('account_success', 'Paiement confirmé. Votre abonnement est actif.');
            }

            return redirect()
                ->route('account', ['tab' => 'purchases'])
                ->with('account_success', 'Paiement Djomy confirmé. Vos contenus sont disponibles dans votre compte.');
        }

        if ($transaction->isSubscription() && $transaction->subscriptionProductId()) {
            return redirect()
                ->route('subscriptions.show', $transaction->subscriptionProductId())
                ->with('error', 'Le paiement n\'a pas encore été confirmé. Réessayez dans quelques instants.');
        }

        return redirect()
            ->route('cart')
            ->with('error', 'Le paiement n\'a pas encore été confirmé. Réessayez dans quelques instants ou contactez le support.');
    }

    public function cancel(string $reference): RedirectResponse
    {
        $transaction = PaymentTransaction::query()
            ->where('reference', $reference)
            ->firstOrFail();

        if ($transaction->status !== PaymentTransaction::STATUS_COMPLETED) {
            $transaction->update(['status' => PaymentTransaction::STATUS_CANCELLED]);
        }

        if ($transaction->isSubscription() && $transaction->subscriptionProductId()) {
            return redirect()
                ->route('subscriptions.show', $transaction->subscriptionProductId())
                ->with('error', 'Paiement annulé.');
        }

        return redirect()
            ->route('cart')
            ->with('error', 'Paiement annulé. Votre panier est conservé.');
    }

    public function webhook(Request $request): Response|JsonResponse
    {
        $rawBody = $request->getContent();
        $signature = (string) $request->header(config('djomy.webhook_signature_header', 'x-webhook-signature'));

        if (! $signature || ! $this->client->verifyWebhookSignature($rawBody, $signature)) {
            return response()->json(['message' => 'Signature invalide'], 401);
        }

        $payload = json_decode($rawBody, true);

        if (! is_array($payload)) {
            return response()->json(['message' => 'Payload invalide'], 400);
        }

        if (($payload['eventType'] ?? '') === 'payment.success') {
            $transactionId = $payload['data']['transactionId'] ?? null;
            $merchantRef = $payload['data']['merchantPaymentReference'] ?? null;

            $transaction = null;

            if ($merchantRef) {
                $transaction = PaymentTransaction::query()->where('reference', $merchantRef)->first();
            }

            if (! $transaction && $transactionId) {
                $transaction = PaymentTransaction::query()
                    ->where('djomy_transaction_id', $transactionId)
                    ->first();
            }

            if ($transaction) {
                try {
                    $this->djomyPayments->fulfillIfPaid($transaction);
                } catch (\Throwable $exception) {
                    Log::error('Djomy webhook fulfillment failed', [
                        'reference' => $transaction->reference,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return response()->noContent();
    }
}
