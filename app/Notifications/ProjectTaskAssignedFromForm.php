<?php

namespace App\Notifications;

use App\Models\CustomFormSubmission;
use App\Models\ProjectTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectTaskAssignedFromForm extends Notification
{
    use Queueable;

    public function __construct(
        public ProjectTask $task,
        public CustomFormSubmission $submission,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $template = $this->submission->template;

        return [
            'type' => 'project_task_assigned_from_form',
            'title' => __('New Operational Task'),
            'task_id' => $this->task->id,
            'project_id' => $this->task->project_id,
            'custom_form_submission_id' => $this->submission->id,
            'custom_form_template_id' => $this->submission->custom_form_template_id,
            'message' => __('A new task was created from :form.', [
                'form' => $template?->title ?? __('a custom form'),
            ]),
            'url' => route('my-tasks', absolute: false),
        ];
    }
}
