<?php

namespace FluxErp\Rulesets\Project;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\Project;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\Project\ProjectState;
use Spatie\ModelStates\Validation\ValidStateRule;

class CreateProjectRuleset extends FluxRuleset
{
    protected static ?string $model = Project::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:projects,uuid',
            'client_id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
            'contact_id' => [
                'integer',
                'nullable',
                new ModelExists(Contact::class),
            ],
            'order_id' => [
                'integer',
                'nullable',
                new ModelExists(Order::class),
            ],
            'responsible_user_id' => [
                'integer',
                'nullable',
                new ModelExists(User::class),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                new ModelExists(Project::class),
            ],
            'project_number' => 'nullable|string',
            'name' => 'required|string',
            'start_date' => 'date_format:Y-m-d|nullable',
            'end_date' => 'date_format:Y-m-d|nullable',
            'description' => 'string|nullable',
            'state' => [
                'string',
                ValidStateRule::make(ProjectState::class),
            ],
            'time_budget' => 'nullable|regex:/[0-9]*:[0-5][0-9]/',
            'budget' => 'numeric|nullable|min:0',
        ];
    }
}
