<?php

namespace FluxErp\Support\Mentions;

use FluxErp\Contracts\MentionsContent;
use FluxErp\Models\Mention;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MentionSync
{
    public function __construct(private readonly MentionParser $parser) {}

    /**
     * @return object{added: array<int, array<string, mixed>>, removed: Collection<int, Mention>}
     */
    public function sync(Model&MentionsContent $source): object
    {
        $text = $source->mentionScannableText();

        $members = method_exists($source, 'mentionableMembersScope')
            ? ($source->mentionableMembersScope() ?? collect())
            : collect();

        $parsed = $this->parser->parse($text, $members);

        $existing = Mention::query()
            ->where('mention_source_type', $source->getMorphClass())
            ->where('mention_source_id', $source->getKey())
            ->get();

        $existingKeys = $existing->map(fn (Mention $m) => $this->keyForExisting($m))->all();
        $parsedKeys = array_map(fn (array $p) => $this->keyForParsed($p), $parsed);

        $toCreate = [];
        foreach ($parsed as $i => $row) {
            if (in_array($parsedKeys[$i], $existingKeys, true)) {
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
            fn (Mention $m) => in_array($this->keyForExisting($m), $parsedKeys, true)
        );

        if ($toDelete->isNotEmpty()) {
            Mention::whereKey($toDelete->pluck('id'))->delete();
        }
        if ($toCreate !== []) {
            Mention::insert($toCreate);
        }

        return (object) ['added' => $toCreate, 'removed' => $toDelete];
    }

    protected function keyForExisting(Mention $m): string
    {
        $type = is_object($m->mention_type_enum)
            ? $m->mention_type_enum->value
            : (string) ($m->mention_type_enum ?? '');

        return implode('|', [
            $type,
            (string) ($m->mention_target_type ?? ''),
            (string) ($m->mention_target_id ?? ''),
            (string) ($m->user_id ?? ''),
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function keyForParsed(array $row): string
    {
        return implode('|', [
            $row['type'],
            (string) ($row['mentionable_type'] ?? ''),
            (string) ($row['mentionable_id'] ?? ''),
            (string) ($row['user_id'] ?? ''),
        ]);
    }
}
