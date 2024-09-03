<?php

namespace FluxErp\Rulesets\Project;

use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\Project;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\ValidStateRule;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\Project\ProjectState;

class UpdateProjectRuleset extends FluxRuleset
{
    protected static ?string $model = Project::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Project::class]),
            ],
            'contact_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'order_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'responsible_user_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'project_number' => 'sometimes|required|string',
            'name' => 'sometimes|required|string',
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
            'description' => 'string|nullable',
            'state' => [
                'string',
                ValidStateRule::make(ProjectState::class),
            ],
            'progress' => 'integer|nullable|min:0|max:100',
            'time_budget' => 'nullable|regex:/[0-9]*:[0-5][0-9]/',
            'budget' => 'numeric|nullable|min:0',
        ];
    }
}
