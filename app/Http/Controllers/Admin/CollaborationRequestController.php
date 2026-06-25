<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollaborationRequest;
use App\Services\CollaborationRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollaborationRequestController extends Controller
{
    public function __construct(
        private CollaborationRequestService $requestService
    ) {}

    public function index(Request $request): View
    {
        $query = CollaborationRequest::query()->with(['investigation', 'user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return view('admin.collaboration.index', [
            'requests' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function show(CollaborationRequest $collaboration): View
    {
        $collaboration->load(['investigation', 'user']);

        return view('admin.collaboration.show', [
            'request' => $collaboration,
        ]);
    }

    public function update(Request $request, CollaborationRequest $collaboration): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,accepted,rejected'],
        ]);

        try {
            $this->requestService->updateStatus($collaboration, $data['status']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('admin.collaboration.show', $collaboration)
            ->with('success', 'Statut mis à jour.');
    }

    public function destroy(CollaborationRequest $collaboration): RedirectResponse
    {
        $collaboration->delete();

        return redirect()->route('admin.collaboration.index')
            ->with('success', 'Candidature supprimée.');
    }
}
