<?php

namespace FluxErp\Actions\MailMessage;

use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Mail\GenericMail;
use FluxErp\Models\Communication;
use FluxErp\Models\EmailTemplate;
use FluxErp\Rulesets\MailMessage\SendMailRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Laravel\SerializableClosure\SerializableClosure;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class SendMail extends DispatchableFluxAction
{
    protected mixed $compiledBladeParameters = null;

    public static function models(): array
    {
        return [Communication::class];
    }

    protected function getRulesets(): string|array
    {
        return SendMailRuleset::class;
    }

    public function performAction(): array
    {
        $mail = GenericMail::make($this->data, $this->compiledBladeParameters);

        try {
            $message = Mail::to($this->getData('to'))
                ->cc($this->getData('cc') ?? [])
                ->bcc($this->getData('bcc') ?? []);

            if ($this->getData('queue', false)) {
                $message->queue($mail);
            } else {
                $message->send($mail);
            }

            return [
                'success' => true,
                'message' => __('Email(s) sent successfully!'),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => __('Failed to send email!'),
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function applyTemplate(
        EmailTemplate $template,
        array|SerializableClosure|null $bladeParameters
    ): void {
        $templateData = $bladeParameters instanceof SerializableClosure
            ? $bladeParameters->getClosure()()
            : $bladeParameters ?? [];

        $renderedSubject = html_entity_decode($template->subject ?? '');
        $renderedHtmlBody = html_entity_decode($template->html_body ?? '');
        $renderedTextBody = html_entity_decode($template->text_body ?? '');

        if ($templateData) {
            $renderedSubject = Blade::render($renderedSubject, $templateData);
            $renderedHtmlBody = Blade::render($renderedHtmlBody, $templateData);
            $renderedTextBody = Blade::render($renderedTextBody, $templateData);
        }

        $this->data['subject'] = $this->getData('subject') ?: $renderedSubject;
        $this->data['html_body'] = $this->getData('html_body') ?: $renderedHtmlBody;
        $this->data['text_body'] = $this->getData('text_body') ?: $renderedTextBody;
        $this->data['to'] = $this->getData('to') ?: ($template->to ?? []);
        $this->data['cc'] = array_unique(array_merge($this->getData('cc') ?? [], $template->cc ?? []));
        $this->data['bcc'] = array_unique(array_merge($this->getData('bcc') ?? [], $template->bcc ?? []));

        $templateAttachments = $template->getMedia()
            ->map(fn (Media $media) => [
                'id' => $media->getKey(),
                'name' => $media->file_name,
            ])
            ->toArray();

        $this->data['attachments'] = array_merge(
            $this->getData('attachments') ?? [],
            $templateAttachments
        );
    }

    protected function prepareForValidation(): void
    {
        $this->data['to'] = Arr::wrap($this->getData('to') ?? []);
        $this->data['cc'] = Arr::wrap($this->getData('cc') ?? []);
        $this->data['bcc'] = Arr::wrap($this->getData('bcc') ?? []);
    }

    protected function validateData(): void
    {
        Validator::validate(
            $this->getData(),
            Arr::only(
                $this->getRules(),
                [
                    'template_id',
                    'blade_parameters',
                    'blade_parameters_serialized',
                ]
            )
        );

        $bladeParameters = data_get($this->data, 'blade_parameters');
        if (data_get($this->data, 'blade_parameters_serialized') && is_string($bladeParameters)) {
            $bladeParameters = unserialize($bladeParameters);
        }
        $this->compiledBladeParameters = $bladeParameters;

        $templateId = data_get($this->data, 'template_id');
        if ($templateId) {
            $template = resolve_static(EmailTemplate::class, 'query')
                ->whereKey($templateId)
                ->first();

            if ($template) {
                $this->applyTemplate($template, $bladeParameters);
            }
        }

        parent::validateData();
    }
}
