<div class="flex gap-4">
    <x-card class="flex flex-col gap-4">
        @foreach($models as $alias => $model)
            <x-button wire:click="select('{{ $alias }}')">
                {{ __(\Illuminate\Support\Str::headline($alias)) }}
            </x-button>
        @endforeach
    </x-card>
    <x-card class="flex flex-col gap-6" x-cloak x-show="$wire.model">
        <div class="flex gap-4">
            <x-card :title="__('Ranking Rules')">
                <ul x-sort class="flex flex-col gap-1">
                    @foreach(data_get($settings, 'rankingRules', []) as $rankingRule)
                        <li x-sort:item class="cursor-move">
                            <x-badge :label="$rankingRule"/>
                        </li>
                    @endforeach
                </ul>
            </x-card>
            <x-card :title="__('Settings')">
                <x-native-select
                    wire:model="settings.proximityPrecision"
                    :label="__('Proximity Precision')"
                    :options="[null, 'byWord', 'byAttribute']"
                />
            </x-card>
        </div>
        <div class="flex gap-6">
            <x-card :title="__('Attributes')">
                <ul x-sort>
                    @foreach($availableAttributes as $availableAttribute)
                        <li x-sort:item>
                            <x-toggle
                                :id="$model . '-' . $availableAttribute"
                                :value="$availableAttribute"
                                :disabled="$keyName === $availableAttribute"
                                wire:model="searchableAttributes"
                            >
                                <x-slot:label>
                                    <x-label class="cursor-move" :label="__(\Illuminate\Support\Str::headline($availableAttribute))"/>
                                </x-slot:label>
                            </x-toggle>
                        </li>
                    @endforeach
                </ul>
            </x-card>
            <x-card :title="__('Synonyms')">

            </x-card>
            <x-card :title="__('Separator tokens')" class="flex flex-col gap-4">
                <div class="flex gap-1">
                    <template x-for="separatorToken in $wire.settings.separatorTokens">
                        <x-badge>
                            <div x-text="separatorToken">
                            </div>
                            <x-slot:append>
                                <x-button.circle
                                    flat
                                    xs
                                    icon="x"
                                    class="w-2 h-2"
                                    x-on:click="$wire.removeStringFromSetting(separatorToken, 'separatorTokens')"
                                />
                            </x-slot:append>
                        </x-badge>
                    </template>
                </div>
                <x-input x-ref="separatorToken"/>
                <x-button
                    class="w-full"
                    primary
                    :label="__('Add')"
                    wire:click="addStringToSetting($refs.separatorToken.value, 'separatorTokens'); $refs.separatorToken.value = ''"
                />
            </x-card>
            <x-card :title="__('Non separator tokens')" class="flex flex-col gap-4">
                <div class="flex gap-1">
                    <template x-for="nonSeparatorToken in $wire.settings.nonSeparatorTokens">
                        <x-badge>
                            <div x-text="nonSeparatorToken">
                            </div>
                            <x-slot:append>
                                <x-button.circle
                                    flat
                                    xs
                                    icon="x"
                                    class="w-2 h-2"
                                    x-on:click="$wire.removeStringFromSetting(nonSeparatorToken, 'separatorTokens')"
                                />
                            </x-slot:append>
                        </x-badge>
                    </template>
                </div>
                <x-input x-ref="nonSeparatorToken"/>
                <x-button
                    class="w-full"
                    primary
                    :label="__('Add')"
                    wire:click="addStringToSetting($refs.nonSeparatorToken.value, 'nonSeparatorTokens'); $refs.nonSeparatorToken.value = ''"
                />
            </x-card>
            <x-card :title="__('Stop words')" class="flex flex-col gap-4">
                <div class="flex gap-1">
                    <template x-for="stopWords in $wire.settings.stopWords">
                        <x-badge>
                            <div x-text="stopWords">
                            </div>
                            <x-slot:append>
                                <x-button.circle
                                    flat
                                    xs
                                    icon="x"
                                    class="w-2 h-2"
                                    x-on:click="$wire.removeStringFromSetting(stopWords, 'separatorTokens')"
                                />
                            </x-slot:append>
                        </x-badge>
                    </template>
                </div>
                <x-input x-ref="stopWords"/>
                <x-button
                    class="w-full"
                    primary
                    :label="__('Add')"
                    wire:click="addStringToSetting($refs.stopWords.value, 'stopWords'); $refs.stopWords.value = ''"
                />
            </x-card>
        </div>
        <x-slot:footer class="justify-end">
            <x-button
                primary
                :label="__('Save')"
                wire:click="save"
            />
        </x-slot:footer>
    </x-card>
</div>
