<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Settings\SecuritySettings;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class TwoFactorSetup extends Component
{
    use Actions;

    public ?string $confirmCode = null;

    #[Locked]
    public bool $showSetup = false;

    #[Locked]
    public bool $isForced = false;

    #[Locked]
    public bool $isTwoFactorEnabled = false;

    #[Locked]
    public ?string $qrCodeSvg = null;

    #[Locked]
    public ?string $secretKey = null;

    public function mount(): void
    {
        $this->isTwoFactorEnabled = auth()->user()->hasTwoFactorEnabled();
        $this->isForced = auth()->user()->force_two_factor
            || app(SecuritySettings::class)->force_two_factor;
    }

    public function render(): View
    {
        return view('flux::livewire.settings.two-factor-setup');
    }

    #[Renderless]
    public function startSetup(): void
    {
        $twoFactorAuth = auth()->user()?->createTwoFactorAuth();

        $this->qrCodeSvg = $twoFactorAuth?->toQr();
        $this->secretKey = $twoFactorAuth?->toString();
        $this->showSetup = true;
    }

    #[Renderless]
    public function confirmSetup(): void
    {
        if (is_null($this->confirmCode) || ! auth()->user()?->confirmTwoFactorAuth($this->confirmCode)) {
            $this->reset('confirmCode');
            $this->toast()
                ->error(__('Invalid verification code'))
                ->send();

            return;
        }

        $this->showSetup = false;
        $this->isTwoFactorEnabled = true;
        $this->reset('confirmCode', 'qrCodeSvg', 'secretKey');
        $this->toast()
            ->success(__('Two-factor authentication enabled'))
            ->send();
    }

    #[Renderless]
    public function cancelSetup(): void
    {
        if ($this->isForced && $this->isTwoFactorEnabled) {
            $this->toast()
                ->error(__('Two-factor authentication is required for this account'))
                ->send();

            return;
        }

        auth()->user()?->disableTwoFactorAuth();
        $this->showSetup = false;
        $this->reset('confirmCode', 'qrCodeSvg', 'secretKey');
    }

    #[Renderless]
    public function disableTwoFactor(): void
    {
        if ($this->isForced) {
            $this->toast()
                ->error(__('Two-factor authentication is required for this account'))
                ->send();

            return;
        }

        auth()->user()?->disableTwoFactorAuth();
        $this->isTwoFactorEnabled = false;
        $this->toast()
            ->success(__('Two-factor authentication disabled'))
            ->send();
    }
}
