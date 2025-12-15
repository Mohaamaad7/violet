<?php

namespace App\Livewire\Store;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactForm extends Component
{
    #[Validate('required|min:3')]
    public $name = '';

    #[Validate('required|email')]
    public $email = '';

    #[Validate('required|regex:/^01[0-9]{9}$/')]
    public $phone = '';

    #[Validate('required|min:3')]
    public $subject = '';

    #[Validate('required|min:10')]
    public $message = '';

    public $sending = false;

    public function submit()
    {
        $this->validate();

        $this->sending = true;

        try {
            // Send email to admin
            $adminEmail = config('mail.admin_email', config('mail.from.address'));

            Mail::send('emails.contact', [
                'contactName' => $this->name,
                'contactEmail' => $this->email,
                'contactPhone' => $this->phone,
                'contactSubject' => $this->subject,
                'contactMessage' => $this->message,
            ], function ($mail) use ($adminEmail) {
                $mail->to($adminEmail)
                    ->subject('رسالة جديدة من نموذج الاتصال - ' . $this->subject);
                $mail->replyTo($this->email, $this->name);
            });

            // Log the contact form submission
            Log::info('Contact form submitted', [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'subject' => $this->subject,
            ]);

            // Reset form
            $this->reset(['name', 'email', 'phone', 'subject', 'message']);

            // Show success message
            $this->dispatch('show-toast', [
                'message' => __('contact.contact_form.success'),
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error('Contact form error: ' . $e->getMessage());

            $this->dispatch('show-toast', [
                'message' => __('contact.contact_form.error'),
                'type' => 'error'
            ]);
        } finally {
            $this->sending = false;
        }
    }

    public function render()
    {
        return view('livewire.store.contact-form');
    }
}
