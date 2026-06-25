<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentOrder;
use App\Models\Taxonomy;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentOrderController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function create(): View
    {
        return view('pages.order-content', [
            'types' => Content::typeLabels(),
            'themes' => Content::themeLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'type' => ['nullable', Rule::exists('taxonomies', 'slug')->where('kind', Taxonomy::KIND_TYPE)->where('is_active', true)],
            'theme' => ['nullable', Rule::exists('taxonomies', 'slug')->where('kind', Taxonomy::KIND_THEME)->where('is_active', true)],
            'country' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'deadline' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $data['user_id'] = Auth::id();
        $data['status'] = ContentOrder::STATUS_PENDING;

        $order = ContentOrder::create($data);

        if (Auth::check()) {
            $this->notifications->notify(
                Auth::user(),
                'order_created',
                'Commande envoyée',
                'Votre demande « '.$order->title.' » a été transmise à la communauté.',
                route('account', ['tab' => 'orders'])
            );
        }

        return redirect()->route('order-content.create')
            ->with('order_success', 'Votre commande a été envoyée. Un journaliste ou notre équipe vous recontactera sous peu.');
    }
}
