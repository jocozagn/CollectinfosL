<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentOrder;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentOrderController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function index(Request $request): View
    {
        $query = ContentOrder::query()->with(['buyer', 'journalist'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('admin.orders.index', [
            'orders' => $query->paginate(15)->withQueryString(),
            'statuses' => ContentOrder::statusLabels(),
        ]);
    }

    public function show(ContentOrder $order): View
    {
        $order->load(['buyer', 'journalist']);

        return view('admin.orders.show', [
            'order' => $order,
            'journalists' => User::query()->where('role', 'journalist')->orderBy('name')->get(),
            'statuses' => ContentOrder::statusLabels(),
        ]);
    }

    public function update(Request $request, ContentOrder $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(ContentOrder::statusLabels()))],
            'assigned_journalist_id' => ['nullable', 'exists:users,id'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'delivery_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $order->update([
            'status' => $data['status'],
            'assigned_journalist_id' => $data['assigned_journalist_id'] ?? $order->assigned_journalist_id,
            'admin_note' => $data['admin_note'] ?? $order->admin_note,
            'delivery_note' => $data['delivery_note'] ?? $order->delivery_note,
            'completed_at' => $data['status'] === ContentOrder::STATUS_COMPLETED ? now() : $order->completed_at,
        ]);

        $this->notifications->notify(
            $order->buyer,
            'order_updated',
            'Mise à jour de commande',
            'Votre commande « '.$order->title.' » : '.$order->statusLabel().'.',
            route('account', ['tab' => 'orders'])
        );

        if ($order->assigned_journalist_id) {
            $journalist = User::find($order->assigned_journalist_id);
            if ($journalist) {
                $this->notifications->notify(
                    $journalist,
                    'order_assigned',
                    'Nouvelle commande',
                    'Une commande vous a été assignée : « '.$order->title.' ».',
                    route('account', ['tab' => 'received_orders'])
                );
            }
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Commande mise à jour.');
    }
}
