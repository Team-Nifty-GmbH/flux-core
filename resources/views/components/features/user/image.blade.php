@props([
    'user' => $user,
    'color' => \FluxErp\Helpers\FrontendHelper::stringToTailwindColor($user->uuid ?? Str::uuid()),
])
<x-avatar :label="strtoupper($user->user_code)" />
