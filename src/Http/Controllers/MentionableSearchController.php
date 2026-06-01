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

        $query = (string) $request->input('q', '');
        $user = $request->user();
        $results = collect();
        $userKey = morph_alias(User::class);

        foreach ($requested as $key) {
            $class = $typesByKey[$key];

            $candidates = $class::searchMentionCandidates($query, 5)
                ->filter(fn ($record): bool => $user ? $user->can('view', $record) : true)
                ->values();

            foreach ($candidates as $record) {
                try {
                    $url = $record->getMentionUrl();
                } catch (Throwable) {
                    continue;
                }

                $results->push([
                    'token' => ($key === $userKey ? '@' : '#') . $key . ':' . $record->getKey(),
                    'label' => $record->getMentionLabel(),
                    'type_key' => $key,
                    'type_label' => $class::mentionTypeLabel(),
                    'type_icon' => $class::mentionTypeIcon(),
                    'url' => $url,
                ]);
            }
        }

        return response()->json($results->all());
    }
}
