<x-input
    autofocus
    placeholder="e.g. john.doe@example.com…"
    wire:model="userForm.email"
    :label="__('Email')"
/>
<x-input
    placeholder="e.g. John…"
    wire:model="userForm.firstname"
    :label="__('Firstname')"
/>
<x-input
    placeholder="e.g. Doe…"
    wire:model="userForm.lastname"
    :label="__('Lastname')"
/>
<x-input
    placeholder="e.g. JD…"
    wire:model="userForm.user_code"
    :label="__('User code')"
/>
<x-password
    placeholder="minimum 8 characters, uppercase, lowercase, special character…"
    wire:model="userForm.password"
    :label="__('Password')"
/>
<x-password
    wire:model="userForm.password_confirmation"
    :label="__('Repeat password')"
/>
