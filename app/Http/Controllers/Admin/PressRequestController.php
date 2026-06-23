<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PressRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PressRequestController extends Controller
{
    public function index(): View
    {
        return view('admin.press-requests.index', [
            'requests' => PressRequest::query()->latest()->paginate(15),
        ]);
    }

    public function show(PressRequest $pressRequest): View
    {
        return view('admin.press-requests.show', [
            'request' => $pressRequest,
        ]);
    }

    public function destroy(PressRequest $pressRequest): RedirectResponse
    {
        $pressRequest->delete();

        return redirect()->route('admin.press-requests.index')
            ->with('success', 'Demande presse supprimée.');
    }
}
