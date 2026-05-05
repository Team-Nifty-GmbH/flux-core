<?php

namespace FluxErp\Livewire\Auth;

use FluxErp\Enums\ForceTwoFactorMethodEnum;
use FluxErp\Settings\SecuritySettings;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ForceTwoFactorSetup extends Component
{
    use Actions;

    public ?string $confirmCode = null;

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
        $user = auth()->user();
        $user->createTwoFactorAuth();

        $this->qrCodeSvg = $user->twoFactorAuth?->toQr();
        $this->secretKey = $user->twoFactorAuth?->shared_secret;
        $this->method = ForceTwoFactorMethodEnum::Totp;
    }

    public function selectPasskey(): void
    {
        $this->method = ForceTwoFactorMethodEnum::Passkey;
    }

    public function back(): void
    {
        if ($this->method === ForceTwoFactorMethodEnum::Totp) {
            auth()->user()->disableTwoFactorAuth();
        }

        $this->reset('confirmCode', 'qrCodeSvg', 'secretKey', 'method');
    }

    public function confirmTotp(): void
    {
        if (! auth()->user()->confirmTwoFactorAuth($this->confirmCode)) {
            $this->reset('confirmCode');
            $this->toast()
                ->error(__('Invalid code'))
                ->send();

            return;
        }

        $this->redirect(route('dashboard'));
    }

    public function passkeyStored(): void
    {
        if (! auth()->user()->passkeys()->exists()) {
            $this->toast()
                ->error(__('Register a passkey first'))
                ->send();

            return;
        }

        $this->redirect(route('dashboard'));
    }
}
