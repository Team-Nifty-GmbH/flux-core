<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class TwoFactorSetup extends Component
{
    use Actions;

    public ?string $confirmCode = null;

    public bool $isForced = false;

    public bool $isTwoFactorEnabled = false;

    public ?string $qrCodeSvg = null;

    public ?string $secretKey = null;

    public bool $showSetup = false;

    public function mount(): void
    {
        $this->isTwoFactorEnabled = auth()->user()->hasTwoFactorEnabled();
        $this->isForced = (bool) auth()->user()->force_two_factor;
    }

    public function render(): View
    {
        return view('flux::livewire.settings.two-factor-setup');
    }

    #[Renderless]
    public function startSetup(): void
    {
        auth()->user()->createTwoFactorAuth();
        $this->qrCodeSvg = auth()->user()->twoFactorAuth?->toQr();
        $this->secretKey = auth()->user()->twoFactorAuth?->shared_secret;
        $this->showSetup = true;
    }

    #[Renderless]
    public function confirmSetup(): void
    {
        if (auth()->user()->confirmTwoFactorAuth($this->confirmCode)) {
            $this->showSetup = false;
            $this->isTwoFactorEnabled = true;
            $this->reset('confirmCode', 'qrCodeSvg', 'secretKey');
            $this->toast()
                ->success(__('Two-factor authentication enabled'))
                ->send();

            return;
        }

        $this->reset('confirmCode');
        $this->toast()
            ->error(__('Invalid code'))
            ->send();
    }

    #[Renderless]
    public function cancelSetup(): void
    {
        auth()->user()->disableTwoFactorAuth();
        $this->showSetup = false;
        $this->reset('confirmCode', 'qrCodeSvg', 'secretKey');
    }

    #[Renderless]
    public function disableTwoFactor(): void
    {
        auth()->user()->disableTwoFactorAuth();
        $this->isTwoFactorEnabled = false;
        $this->toast()
            ->success(__('Two-factor authentication disabled'))
            ->send();
    }
}
