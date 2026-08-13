<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListOpportunitiesRequest;
use App\Http\Requests\StoreOpportunityRequest;
use App\Http\Requests\UpdateOpportunityRequest;
use App\Http\Requests\UpdateOpportunityStatusRequest;
use App\Http\Resources\OpportunityEventResource;
use App\Http\Resources\OpportunityResource;
use App\Models\Opportunity;
use App\Models\User;
use App\Opportunities\DeadlineInput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class OpportunityController extends Controller
{
    public function index(ListOpportunitiesRequest $request): AnonymousResourceCollection
    {
        $user = $this->user($request);
        $filters = $request->validated();
        $query = Opportunity::query()->ownedBy($user);

        ($filters['archived'] ?? false)
            ? $query->whereNotNull('archived_at')
            : $query->whereNull('archived_at');

        if (($filters['q'] ?? '') !== '') {
            $term = '%'.$filters['q'].'%';

            $query->where(function ($query) use ($term): void {
                $query->where('title', 'ilike', $term)
                    ->orWhere('organization', 'ilike', $term);
            });
        }

        foreach (['status', 'type', 'priority'] as $field) {
            if (array_key_exists($field, $filters)) {
                $query->where($field, $filters[$field]);
            }
        }

        if (isset($filters['deadline_from'])) {
            $query->where(
                'deadline_at',
                '>=',
                DeadlineInput::dateBoundary($filters['deadline_from'], $user->timezone, false),
            );
        }

        if (isset($filters['deadline_to'])) {
            $query->where(
                'deadline_at',
                '<=',
                DeadlineInput::dateBoundary($filters['deadline_to'], $user->timezone, true),
            );
        }

        $opportunities = $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(perPage: 20)
            ->withQueryString();

        return OpportunityResource::collection($opportunities);
    }

    public function store(StoreOpportunityRequest $request): OpportunityResource
    {
        $user = $this->user($request);

        $opportunity = DB::transaction(function () use ($request, $user): Opportunity {
            $validated = $request->validated();
            $attributes = [
                ...DeadlineInput::withoutRawFields($validated),
                ...DeadlineInput::attributes($validated, $user),
                'status' => 'SAVED',
            ];

            $opportunity = $user->opportunities()->create($attributes);
            $this->appendEvent($opportunity, $user, 'CREATED');

            return $opportunity;
        });

        return new OpportunityResource($opportunity);
    }

    public function show(Request $request, string $id): OpportunityResource
    {
        return new OpportunityResource($this->owned($request, $id));
    }

    public function update(UpdateOpportunityRequest $request, string $id): OpportunityResource
    {
        $opportunity = DB::transaction(function () use ($request, $id): Opportunity {
            $user = $this->user($request);
            $opportunity = $this->ownedForUpdate($request, $id);
            $validated = $request->validated();
            $attributes = [
                ...DeadlineInput::withoutRawFields($validated),
                ...DeadlineInput::attributes($validated, $user),
            ];

            $opportunity->fill($attributes);
            $changedFields = array_keys($opportunity->getDirty());

            if ($changedFields !== []) {
                sort($changedFields);
                $opportunity->save();
                $this->appendEvent(
                    $opportunity,
                    $user,
                    'UPDATED',
                    changedFields: $changedFields,
                );
            }

            return $opportunity->refresh();
        });

        return new OpportunityResource($opportunity);
    }

    public function updateStatus(UpdateOpportunityStatusRequest $request, string $id): OpportunityResource
    {
        $opportunity = DB::transaction(function () use ($request, $id): Opportunity {
            $user = $this->user($request);
            $opportunity = $this->ownedForUpdate($request, $id);
            $validated = $request->validated();
            $newStatus = $validated['status'];
            $oldStatus = $opportunity->status;

            if ($newStatus !== $oldStatus) {
                $opportunity->status = $newStatus;
                $opportunity->save();
                $this->appendEvent(
                    $opportunity,
                    $user,
                    'STATUS_CHANGED',
                    fromStatus: $oldStatus,
                    toStatus: $newStatus,
                );
            }

            return $opportunity->refresh();
        });

        return new OpportunityResource($opportunity);
    }

    public function events(Request $request, string $id): AnonymousResourceCollection
    {
        $opportunity = $this->owned($request, $id);
        $events = $opportunity->events()
            ->orderByDesc('created_at')
            ->get();

        return OpportunityEventResource::collection($events);
    }

    public function destroy(Request $request, string $id): Response
    {
        DB::transaction(function () use ($request, $id): void {
            $this->ownedForUpdate($request, $id)->delete();
        });

        return response()->noContent();
    }

    public function archive(Request $request, string $id): OpportunityResource
    {
        $opportunity = DB::transaction(function () use ($request, $id): Opportunity {
            $user = $this->user($request);
            $opportunity = $this->ownedForUpdate($request, $id);

            if ($opportunity->archived_at === null) {
                $opportunity->archived_at = now();
                $opportunity->save();
                $this->appendEvent($opportunity, $user, 'ARCHIVED');
            }

            return $opportunity->refresh();
        });

        return new OpportunityResource($opportunity);
    }

    public function restore(Request $request, string $id): OpportunityResource
    {
        $opportunity = DB::transaction(function () use ($request, $id): Opportunity {
            $user = $this->user($request);
            $opportunity = $this->ownedForUpdate($request, $id);

            if ($opportunity->archived_at !== null) {
                $opportunity->archived_at = null;
                $opportunity->save();
                $this->appendEvent($opportunity, $user, 'RESTORED');
            }

            return $opportunity->refresh();
        });

        return new OpportunityResource($opportunity);
    }

    private function appendEvent(
        Opportunity $opportunity,
        User $actor,
        string $type,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?array $changedFields = null,
    ): void {
        $opportunity->events()->create([
            'actor_id' => $actor->getKey(),
            'type' => $type,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_fields' => $changedFields,
        ]);
    }

    private function owned(Request $request, string $id): Opportunity
    {
        return Opportunity::query()
            ->ownedBy($this->user($request))
            ->findOrFail($id);
    }

    private function ownedForUpdate(Request $request, string $id): Opportunity
    {
        return Opportunity::query()
            ->ownedBy($this->user($request))
            ->lockForUpdate()
            ->findOrFail($id);
    }

    private function user(Request $request): User
    {
        /** @var User $user */
        $user = $request->user('web');

        return $user;
    }
}
