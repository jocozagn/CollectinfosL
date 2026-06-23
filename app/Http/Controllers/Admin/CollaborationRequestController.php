<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollaborationRequest;
use App\Services\CollaborationAcceptanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollaborationRequestController extends Controller
{
    public function __construct(
        private CollaborationAcceptanceService $acceptanceService
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

        $previousStatus = $collaboration->status;
        $collaboration->update($data);

        $this->acceptanceService->handleStatusChange($collaboration->fresh(), $previousStatus, $data['status']);

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
