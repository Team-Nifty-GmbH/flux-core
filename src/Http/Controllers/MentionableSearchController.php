<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Models\User;
use FluxErp\Services\Mentions\MentionableTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class MentionableSearchController extends Controller
{
    protected static bool $hasPermission = false;

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'string|nullable',
            'types' => 'array',
            'types.*' => 'string',
        ]);

        $typesByKey = MentionableTypes::map();
        $requested = collect($request->input('types', array_keys($typesByKey)));

        $unknown = $requested->reject(fn (string $key): bool => isset($typesByKey[$key]));
        if ($unknown->isNotEmpty()) {
            abort(422, 'Unknown mentionable types: ' . $unknown->implode(', '));
        }

        $query = trim((string) $request->input('q', ''));

        $scope = null;
        if (preg_match('/^([A-Za-z_]+):(.*)$/s', $query, $matches)) {
            $candidateKey = strtolower($matches[1]);
            if ($requested->contains($candidateKey)) {
                $scope = $candidateKey;
                $query = trim($matches[2]);
            }
        }

        if ($query === '' && $scope === null) {
            return response()->json($this->scopeChips($requested, $typesByKey));
        }

        $searchTypes = $scope !== null ? collect([$scope]) : $requested;
        $user = $request->user();
        $results = collect();
        $userKey = morph_alias(User::class);

        foreach ($searchTypes as $key) {
            $class = $typesByKey[$key];

            try {
                $candidates = $class::searchMentionCandidates($query, 5)
                    ->filter(fn ($record): bool => $user ? $user->can('view', $record) : true)
                    ->values();
            } catch (Throwable $e) {
                report($e);

                continue;
            }

            foreach ($candidates as $record) {
                $results->push([
                    'kind' => 'record',
                    'token' => ($key === $userKey ? '@' : '#') . $key . ':' . $record->getKey(),
                    'label' => $record->getMentionLabel(),
                    'type_key' => $key,
                    'type_label' => $class::mentionTypeLabel(),
                    'type_icon' => $class::mentionTypeIcon(),
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
                'label' => $typesByKey[$key]::mentionTypeLabel(),
                'type_icon' => $typesByKey[$key]::mentionTypeIcon(),
            ])
            ->values()
            ->all();
    }
}
