<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        $exists = NewsletterSubscriber::query()->where('email', $data['email'])->exists();

        if ($exists) {
            return redirect()->to(route('home').'#newsletter')
                ->with('newsletter_info', 'Cette adresse est déjà inscrite à notre newsletter.')
                ->withInput($request->only('email', 'name'));
        }

        NewsletterSubscriber::create([
            'email' => $data['email'],
            'name' => $data['name'] ?? null,
            'subscribed_at' => now(),
        ]);

        return redirect()->to(route('home').'#newsletter')
            ->with('newsletter_success', 'Merci ! Vous êtes inscrit(e) à la newsletter Collectinfos.');
    }
}
