<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Facades\MentionableType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Throwable;

class MentionableSearchController extends Controller
{
    protected static bool $hasPermission = false;

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'string|nullable',
            'types' => 'array',
            'types.*' => 'string',
        ]);

        $typesByKey = MentionableType::all();
        $requested = collect($request->input('types', array_keys($typesByKey)));

        $unknown = $requested->reject(fn (string $key): bool => array_key_exists($key, $typesByKey));
        if ($unknown->isNotEmpty()) {
            abort(422, 'Unknown mentionable types: ' . $unknown->implode(', '));
        }

        $query = trim((string) $request->input('query'));

        $scope = null;
        if (preg_match('/^([A-Za-z_]+):(.*)$/s', $query, $matches)) {
            $candidateKey = strtolower($matches[1]);
            if ($requested->contains($candidateKey)) {
                $scope = $candidateKey;
                $query = trim($matches[2]);
            }
        }

        if ($query === '' && is_null($scope)) {
            return response()->json($this->scopeChips($requested, $typesByKey));
        }

        $searchTypes = ! is_null($scope) ? collect([$scope]) : $requested;
        $user = $request->user();
        $results = collect();
        $userKeys = MentionableType::getUserMentionableTypes(keysOnly: true);

        foreach ($searchTypes as $key) {
            $class = $typesByKey[$key];

            try {
                $candidates = resolve_static($class, 'searchMentionCandidates', [$query, 5])
                    ->filter(fn (Model $record): bool => is_null($user)
                        || is_null(Gate::getPolicyFor($record))
                        || $user->can('view', $record))
                    ->values();
            } catch (Throwable $e) {
                report($e);

                continue;
            }

            foreach ($candidates as $record) {
                $results->push([
                    'kind' => 'record',
                    'token' => (in_array($key, $userKeys, true) ? '@' : '#') . $key . ':' . $record->getKey(),
                    'label' => $record->getMentionLabel(),
                    'type_key' => $key,
                    'type_label' => resolve_static($class, 'mentionTypeLabel'),
                    'type_icon' => resolve_static($class, 'mentionTypeIcon'),
                    'url' => $record->getMentionUrl(),
                ]);
            }
        }

        return response()->json($results->all());
    }

    /**
     * @param  \Illuminate\Support\Collection<int, string>  $requested
     * @param  array<string, class-string>  $typesByKey
     * @return array<int, array{kind: string, scope_key: string, label: string, type_icon: string}>
     */
    protected function scopeChips($requested, array $typesByKey): array
    {
        if ($requested->count() < 2) {
            return [];
        }

        return $requested
            ->map(fn (string $key): array => [
                'kind' => 'scope',
                'scope_key' => $key,
                'label' => resolve_static($typesByKey[$key], 'mentionTypeLabel'),
                'type_icon' => resolve_static($typesByKey[$key], 'mentionTypeIcon'),
            ])
            ->values()
            ->all();
    }
}
