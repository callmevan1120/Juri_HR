<?php

namespace App\Notifications;

use App\Models\CustomFormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomFormSubmittedForReview extends Notification
{
    use Queueable;

    public function __construct(
        public CustomFormSubmission $submission,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $template = $this->submission->template;
        $submitter = $this->submission->submitter;

        return [
            'type' => 'custom_form_submitted_for_review',
            'title' => __('Custom Form Submitted'),
            'custom_form_submission_id' => $this->submission->id,
            'custom_form_template_id' => $this->submission->custom_form_template_id,
            'submitted_by' => $this->submission->submitted_by,
            'message' => __(':user submitted :form.', [
                'user' => $submitter?->name ?? __('Someone'),
                'form' => $template?->title ?? __('a custom form'),
            ]),
            'url' => route('admin.custom-forms', absolute: false),
        ];
    }
}
