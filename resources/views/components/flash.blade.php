@if (session()->has('flash'))
    <script>
        window.addEventListener('livewire:navigated', () => {
            @foreach(\Illuminate\Support\Arr::wrap(session('flash')) as $type => $flash)
                window.$wireui.notify({
                    title: '{{ $flash }}',
                    icon: '{{ in_array($type, ['success', 'error', 'info', 'amber', 'question']) ? $type : 'info' }}',
                    timeout: 0,
                })
            @endforeach
        });
    </script>
@endif
