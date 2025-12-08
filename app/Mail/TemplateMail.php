<?php

namespace App\Mail;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class TemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public EmailLog $emailLog;
    protected string $renderedHtml;
    protected string $emailSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public EmailTemplate $template,
        public array $variables = [],
        public string $locale = 'ar',
        public ?string $recipientName = null,
        public ?Model $related = null,
    ) {
        $this->afterCommit();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $service = App::make(EmailTemplateService::class);
        $this->emailSubject = $service->getSubject($this->template, $this->variables, $this->locale);

        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $service = App::make(EmailTemplateService::class);
        
        $this->renderedHtml = $service->render(
            $this->template,
            $this->variables,
            $this->locale
        );

        return $this->html($this->renderedHtml);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Set the email log for tracking.
     */
    public function withLog(EmailLog $log): self
    {
        $this->emailLog = $log;

        return $this;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        if (isset($this->emailLog)) {
            $this->emailLog->markAsFailed($exception->getMessage());
        }
    }
}
