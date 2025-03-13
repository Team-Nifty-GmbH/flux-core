<x-mail::message>
    {{ __("To finish logging in please click the link below.") }}
    <x-mail::button :$url>
        {{ __("Click to login") }}
    </x-mail::button>
    {{ __("Note: Your magic sign-in link is time-limited and can only be used once. Feel free to delete this email when you are done.") }}
</x-mail::message>
