<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentPurchase;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(): View
    {
        return view('pages.cart', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    public function add(Content $content): RedirectResponse
    {
        if (! $content->isPublished() || ! $content->isPaid()) {
            return back()->with('error', 'Ce contenu ne peut pas être ajouté au panier.');
        }

        if (Auth::check() && Auth::user()->hasPurchased($content)) {
            return back()->with('error', 'Vous possédez déjà ce contenu.');
        }

        $this->cart->add($content);

        return back()->with('cart_success', '« '.$content->title.' » a été ajouté au panier.');
    }

    public function remove(Content $content): RedirectResponse
    {
        $this->cart->remove($content->id);

        return redirect()->route('cart')->with('cart_success', 'Article retiré du panier.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('account')
                ->with('error', 'Connectez-vous pour finaliser votre achat.');
        }

        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Votre panier est vide.');
        }

        $user = Auth::user();
        $purchased = 0;

        DB::transaction(function () use ($items, $user, &$purchased) {
            foreach ($items as $content) {
                if ($user->hasPurchased($content)) {
                    continue;
                }

                ContentPurchase::create([
                    'user_id' => $user->id,
                    'content_id' => $content->id,
                    'price' => $content->price,
                    'purchased_at' => now(),
                ]);

                $purchased++;
            }
        });

        $this->cart->clear();

        $message = $purchased > 0
            ? 'Achat confirmé ! '.$purchased.' contenu(s) sont maintenant disponibles dans votre compte.'
            : 'Tous les articles de votre panier étaient déjà achetés.';

        return redirect()->route('account')->with('account_success', $message);
    }
}
