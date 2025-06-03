<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceRequestStatusUpdated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ServiceRequest $serviceRequest;

    /**
     * Create a new message instance.
     */
    public function __construct(ServiceRequest $serviceRequest)
    {
        $this->serviceRequest = $serviceRequest;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->serviceRequest->status) {
            'approved' => 'Your Service Request Has Been Approved',
            'denied' => 'Update on Your Service Request',
            'completed' => 'Your Service Request Has Been Completed',
            default => 'Update on Your Service Request'
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.service-request-updated',
            with: [
                'serviceRequest' => $this->serviceRequest,
                'clientName' => $this->serviceRequest->client_name,
                'serviceName' => $this->serviceRequest->service->name,
                'status' => $this->serviceRequest->status,
                'adminResponse' => $this->serviceRequest->admin_response,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
