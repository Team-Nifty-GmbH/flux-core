<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        // Fix name = id records
        $tags = DB::table('tags')
            ->join('tags AS joined_tags', function (JoinClause $join): void {
                $join->on('tags.id', '=', 'joined_tags.name')
                    ->whereNull('joined_tags.type');
            })
            ->get(['tags.id', 'joined_tags.id AS joined_id'])
            ->toArray();

        foreach ($tags as $tag) {
            DB::table('taggables')
                ->where('tag_id', $tag->joined_id)
                ->update(['tag_id' => $tag->id]);
        }

        DB::table('tags')
            ->whereIntegerInRaw('id', array_column($tags, 'joined_id'))
            ->delete();

        // Remove duplicate tags
        $duplicates = DB::table('tags')
            ->selectRaw('MIN(id) AS id, name, type')
            ->groupBy('name', 'type')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->toArray();

        foreach ($duplicates as $duplicate) {
            $duplicateTaggables = DB::table('taggables')
                ->join('tags', 'taggables.tag_id', '=', 'tags.id')
                ->where('tags.name', $duplicate->name)
                ->where('tags.type', $duplicate->type)
                ->selectRaw('taggable_type, taggable_id')
                ->groupBy(['taggable_type', 'taggable_id'])
                ->havingRaw('COUNT(*) > 1')
                ->get()
                ->toArray();

            if ($duplicateTaggables) {
                $ids = DB::table('tags')
                    ->where('name', $duplicate->name)
                    ->where('type', $duplicate->type)
                    ->orderBy('id', 'ASC')
                    ->pluck('id')
                    ->toArray();

                DB::table('taggables')
                    ->whereIntegerInRaw('tag_id', $ids)
                    ->whereIntegerInRaw(
                        'taggable_id',
                        array_column($duplicateTaggables, 'taggable_id')
                    )
                    ->delete();

                DB::table('taggables')
                    ->insert(
                        array_map(
                            fn ($taggable) => [
                                'tag_id' => Arr::first($ids),
                                'taggable_type' => $taggable->taggable_type,
                                'taggable_id' => $taggable->taggable_id,
                            ],
                            $duplicateTaggables
                        )
                    );
            } else {
                DB::table('taggables')
                    ->join('tags', 'taggables.tag_id', '=', 'tags.id')
                    ->where('tags.id', '!=', $duplicate->id)
                    ->where('tags.name', $duplicate->name)
                    ->where('tags.type', $duplicate->type)
                    ->update(['taggables.tag_id' => $duplicate->id]);
            }

            DB::table('tags')
                ->where('id', '!=', $duplicate->id)
                ->where('name', $duplicate->name)
                ->where('type', $duplicate->type)
                ->delete();
        }
    }

    public function down(): void {}
};
