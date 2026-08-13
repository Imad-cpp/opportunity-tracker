<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOpportunityRequest;
use App\Http\Requests\UpdateOpportunityRequest;
use App\Http\Resources\OpportunityResource;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class OpportunityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $this->user($request);
        $opportunities = Opportunity::query()
            ->ownedBy($user)
            ->whereNull('archived_at')
            ->orderByDesc('updated_at')
            ->get();

        return OpportunityResource::collection($opportunities);
    }

    public function store(StoreOpportunityRequest $request): OpportunityResource
    {
        $user = $this->user($request);
        $opportunity = $user->opportunities()->create([
            ...$request->validated(),
            'status' => 'SAVED',
        ]);

        return new OpportunityResource($opportunity);
    }

    public function show(Request $request, string $id): OpportunityResource
    {
        return new OpportunityResource($this->owned($request, $id));
    }

    public function update(UpdateOpportunityRequest $request, string $id): OpportunityResource
    {
        $opportunity = $this->owned($request, $id);
        $opportunity->fill($request->validated());
        $opportunity->save();

        return new OpportunityResource($opportunity->refresh());
    }

    public function destroy(Request $request, string $id): Response
    {
        $this->owned($request, $id)->delete();

        return response()->noContent();
    }

    public function archive(Request $request, string $id): OpportunityResource
    {
        $opportunity = $this->owned($request, $id);
        $opportunity->archived_at ??= now();
        $opportunity->save();

        return new OpportunityResource($opportunity->refresh());
    }

    public function restore(Request $request, string $id): OpportunityResource
    {
        $opportunity = $this->owned($request, $id);
        $opportunity->archived_at = null;
        $opportunity->save();

        return new OpportunityResource($opportunity->refresh());
    }

    private function owned(Request $request, string $id): Opportunity
    {
        return Opportunity::query()
            ->ownedBy($this->user($request))
            ->findOrFail($id);
    }

    private function user(Request $request): User
    {
        /** @var User $user */
        $user = $request->user('web');

        return $user;
    }
}
