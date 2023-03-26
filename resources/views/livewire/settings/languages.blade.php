<div class="py-6" x-data="{language: @entangle('selectedLanguage').defer, languages: @entangle('languages').defer}">
    <x-modal.card :title="__('Edit Language')" wire:model.defer="editModal">
        <div class="space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200">
                <div>
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-6">
                        <div class="space-y-3 sm:col-span-6">
                            <x-input wire:model="selectedLanguage.name" :label="__('Language Name')"/>
                            <x-input wire:model="selectedLanguage.iso_name" :label="__('ISO Name')"/>
                            <x-input wire:model="selectedLanguage.language_code" :label="__('Language Code')"
                                     list="language-code-data" autocomplete="off"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-slot name="footer">
            <div class="flex justify-between gap-x-4">
                @if(user_can('api.languages.delete'))
                    <div x-bind:class="language.id > 0 || 'invisible'">
                        <x-button flat negative :label="__('Delete')" x-on:click="close" wire:click="delete"/>
                    </div>
                @endif
                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close"/>
                    <x-button primary :label="__('Save')" wire:click="save"/>
                </div>
            </div>
        </x-slot>
    </x-modal.card>
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold">{{ __('Languages') }}</h1>
                <div class="mt-2 text-sm text-gray-300">{{ __('A list of all the languages') }}</div>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                @if(user_can('api.languages.post'))
                    <x-button primary wire:click="showEditModal()">
                            {{ __('Add Language') }}
                    </x-button>
                @endif
            </div>
        </div>
        <div class="mt-8 flex flex-col">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                            <tr class="divide-x divide-gray-200">
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-4 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                    {{ __('Language Name') }}
                                </th>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-4 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                    {{ __('ISO Name') }}
                                </th>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-4 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                    {{ __('Language Code') }}
                                </th>
                                <th scope="col" class="py-2 pl-2 pr-2 text-left text-sm font-semibold text-gray-900">
                                </th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                            <template x-for="language in languages">
                                <tr class="divide-x divide-gray-200">
                                    <td x-text="language.name"
                                        class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6">
                                    </td>
                                    <td x-text="language.iso_name"
                                        class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6">
                                    </td>
                                    <td x-text="language.language_code"
                                        class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6">
                                    </td>
                                    <td class="whitespace-nowrap py-2 pl-2 pr-2 text-center text-sm text-gray-500">
                                        <x-button x-on:click="$wire.showEditModal(language.id)" type="button"
                                                  icon="pencil"
                                                  class="inline-flex items-center rounded border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        </x-button>
                                    </td>
                                </tr>
                            </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <datalist id="language-code-data">
        <option>af_ZA</option>
        <option>sq_AL</option>
        <option>ar_DZ</option>
        <option>ar_BH</option>
        <option>ar_EG</option>
        <option>ar_IQ</option>
        <option>ar_JO</option>
        <option>ar_KW</option>
        <option>ar_LB</option>
        <option>ar_LY</option>
        <option>ar_MA</option>
        <option>ar_OM</option>
        <option>ar_QA</option>
        <option>ar_SA</option>
        <option>ar_SY</option>
        <option>ar_TN</option>
        <option>ar_AE</option>
        <option>ar_YE</option>
        <option>hy_AM</option>
        <option>Cy_az_AZ</option>
        <option>Lt_az_AZ</option>
        <option>eu_ES</option>
        <option>be_BY</option>
        <option>bg_BG</option>
        <option>ca_ES</option>
        <option>zh_CN</option>
        <option>zh_HK</option>
        <option>zh_MO</option>
        <option>zh_TW</option>
        <option>zh_CHS</option>
        <option>zh_CHT</option>
        <option>hr_HR</option>
        <option>cs_CZ</option>
        <option>da_DK</option>
        <option>div_MV</option>
        <option>nl_BE</option>
        <option>nl_NL</option>
        <option>en_AU</option>
        <option>en_BZ</option>
        <option>en_CA</option>
        <option>en_CB</option>
        <option>en_IE</option>
        <option>en_JM</option>
        <option>en_NZ</option>
        <option>en_PH</option>
        <option>en_ZA</option>
        <option>en_TT</option>
        <option>en_GB</option>
        <option>en_US</option>
        <option>en_ZW</option>
        <option>et_EE</option>
        <option>fo_FO</option>
        <option>fa_IR</option>
        <option>fi_FI</option>
        <option>fr_CA</option>
        <option>fr_FR</option>
        <option>fr_LU</option>
        <option>fr_MC</option>
        <option>fr_MC</option>
        <option>fr_CH</option>
        <option>gl_ES</option>
        <option>ka_GE</option>
        <option>de_AT</option>
        <option>de_DE</option>
        <option>de_LI</option>
        <option>de_LU</option>
        <option>de_CH</option>
        <option>el_GR</option>
        <option>gu_IN</option>
        <option>he_IL</option>
        <option>hi_IN</option>
        <option>hu_HU</option>
        <option>is_IS</option>
        <option>id_ID</option>
        <option>it_IT</option>
        <option>it_CH</option>
        <option>ja_JP</option>
        <option>kn_IN</option>
        <option>kk_KZ</option>
        <option>kok_IN</option>
        <option>ko_KR</option>
        <option>ky_KZ</option>
        <option>lv_LV</option>
        <option>lt_LT</option>
        <option>mk_MK</option>
        <option>ms_BN</option>
        <option>ms_MY</option>
        <option>mr_IN</option>
        <option>mn_MN</option>
        <option>nb_NO</option>
        <option>nn_NO</option>
        <option>pl_PL</option>
        <option>pt_BR</option>
        <option>pt_PT</option>
        <option>pa_IN</option>
        <option>ro_RO</option>
        <option>ru_RU</option>
        <option>sa_IN</option>
        <option>Cy_sr_SP</option>
        <option>Lt_sr_SP</option>
        <option>sk_SK</option>
        <option>sl_SL</option>
        <option>es_AR</option>
        <option>es_BO</option>
        <option>es_CL</option>
        <option>es_CO</option>
        <option>es_CR</option>
        <option>es_DO</option>
        <option>es_EC</option>
        <option>es_SV</option>
        <option>es_GT</option>
        <option>es_HN</option>
        <option>es_MX</option>
        <option>es_NI</option>
        <option>es_PA</option>
        <option>es_PY</option>
        <option>es_PE</option>
        <option>es_PR</option>
        <option>es_ES</option>
        <option>es_UY</option>
        <option>es_VE</option>
        <option>sw_KE</option>
        <option>sv_FI</option>
        <option>sv_SE</option>
        <option>syr_SY</option>
        <option>ta_IN</option>
        <option>tt_RU</option>
        <option>te_IN</option>
        <option>th_TH</option>
        <option>tr_TR</option>
        <option>uk_UA</option>
        <option>ur_PK</option>
        <option>Cy_uz_UZ</option>
        <option>Lt_uz_UZ</option>
        <option>vi_VN</option>
    </datalist>
</div>
