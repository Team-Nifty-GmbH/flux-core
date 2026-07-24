<?php

namespace FluxErp\Support\Mentions;

use FluxErp\Contracts\MentionsContent;
use FluxErp\Models\Mention;
use FluxErp\Support\Collection\UserCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MentionSync
{
    public function __construct(protected readonly MentionParser $parser) {}

    /**
     * @return array{added: array<int, array<string, mixed>>, removed: Collection<int, Mention>}
     */
    public function sync(Model&MentionsContent $source): array
    {
        $text = $source->mentionScannableText();

        $members = method_exists($source, 'mentionableMembersScope')
            ? ($source->mentionableMembersScope() ?? app(UserCollection::class))
            : app(UserCollection::class);

        $parsed = $this->parser->parse($text, $members);

        $existing = resolve_static(Mention::class, 'query')
            ->where('mention_source_type', $source->getMorphClass())
            ->where('mention_source_id', $source->getKey())
            ->get();

        $existingKeys = $existing
            ->map(fn (Mention $mention) => $this->keyForExisting($mention))
            ->all();
        $parsedKeys = array_map(fn (array $parsedRow) => $this->keyForParsed($parsedRow), $parsed);

        $toCreate = [];
        foreach ($parsed as $index => $row) {
            if (in_array($parsedKeys[$index], $existingKeys, true)) {
                continue;
            }

            $toCreate[] = [
                'mention_source_type' => $source->getMorphClass(),
                'mention_source_id' => $source->getKey(),
                'mention_target_type' => $row['mentionable_type'],
                'mention_target_id' => $row['mentionable_id'],
                'mention_type_enum' => $row['type'],
                'user_id' => $row['user_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $toDelete = $existing->reject(
            fn (Mention $mention) => in_array($this->keyForExisting($mention), $parsedKeys, true)
        );

        if ($toDelete->isNotEmpty()) {
            resolve_static(Mention::class, 'query')
                ->whereKey($toDelete->pluck('id'))
                ->delete();
        }

        if ($toCreate !== []) {
            resolve_static(Mention::class, 'query')->insert($toCreate);
        }

        return ['added' => $toCreate, 'removed' => $toDelete];
    }

    protected function keyForExisting(Mention $mention): string
    {
        return implode('|', [
            (string) $mention->mention_type_enum?->value,
            (string) $mention->mention_target_type,
            (string) $mention->mention_target_id,
            (string) $mention->user_id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function keyForParsed(array $row): string
    {
        return implode('|', [
            $row['type'],
            (string) $row['mentionable_type'],
            (string) $row['mentionable_id'],
            (string) $row['user_id'],
        ]);
    }
}
