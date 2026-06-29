<?php

namespace FluxErp\Livewire\Auth;

use FluxErp\Enums\TwoFactorMethodEnum;
use FluxErp\Settings\SecuritySettings;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class ForceTwoFactorSetup extends Component
{
    use Actions;

    public ?string $confirmCode = null;

    #[Locked]
    public ?string $method = null;

    #[Locked]
    public ?string $qrCodeSvg = null;

    #[Locked]
    public ?string $secretKey = null;

    public function mount(): void
    {
        $user = auth()->user();
        $forced = $user
            && ($user->force_two_factor || app(SecuritySettings::class)->force_two_factor);

        if (! $forced || $user->hasTwoFactorMethodConfigured()) {
            $this->redirect(route('dashboard'));
        }
    }

    public function render(): View
    {
        return view('flux::livewire.auth.force-two-factor-setup');
    }

    public function selectTotp(): void
    {
        $twoFactorAuth = auth()->user()?->createTwoFactorAuth();

        $this->qrCodeSvg = $twoFactorAuth?->toQr();
        $this->secretKey = $twoFactorAuth?->toString();
        $this->method = TwoFactorMethodEnum::Totp;
    }

    public function selectPasskey(): void
    {
        $this->method = TwoFactorMethodEnum::Passkey;
    }

    public function back(): void
    {
        if ($this->method === TwoFactorMethodEnum::Totp) {
            auth()->user()?->disableTwoFactorAuth();
        }

        $this->reset('confirmCode', 'qrCodeSvg', 'secretKey', 'method');
    }

    public function backToLogin(): void
    {
        Session::remove('two_factor_login');
        auth()->logout();

        $this->redirect(route('login'));
    }

    #[Renderless]
    public function confirmTotp(): void
    {
        if (is_null($this->confirmCode) || ! auth()->user()?->confirmTwoFactorAuth($this->confirmCode)) {
            $this->reset('confirmCode');
            $this->toast()
                ->error(__('Invalid verification code'))
                ->send();

            return;
        }

        $this->redirect(route('dashboard'));
    }

    #[Renderless]
    public function passkeyStored(): void
    {
        if (! auth()->user()?->passkeys()->exists()) {
            $this->toast()
                ->error(__('Register a passkey first'))
                ->send();

            return;
        }

        $this->redirect(route('dashboard'));
    }
}
