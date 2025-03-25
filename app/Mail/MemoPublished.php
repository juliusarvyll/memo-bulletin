<?php

namespace App\Mail;

use App\Models\Memo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Log;

class MemoPublished extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The memo instance.
     *
     * @var \App\Models\Memo
     */
    public $memo;

    /**
     * The subscriber's email address.
     *
     * @var string
     */
    public $subscriberEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(Memo $memo, string $subscriberEmail)
    {
        $this->memo = $memo;
        $this->subscriberEmail = $subscriberEmail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        Log::info('Building email envelope', [
            'subject' => 'New Memo Published: ' . $this->memo->title,
            'from_address' => $fromAddress,
            'from_name' => $fromName,
            'recipient_email' => $this->subscriberEmail ?? 'Not specified'
        ]);

        return new Envelope(
            subject: 'New Memo Published: ' . $this->memo->title,
            from: new Address($fromAddress, $fromName),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        Log::info('Building email content', [
            'memo_id' => $this->memo->id,
            'view' => 'emails.memos.published',
            'recipient_email' => $this->subscriberEmail ?? 'Not specified'
        ]);

        return new Content(
            view: 'emails.memos.published',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Add the memo image as an attachment if it exists
        if ($this->memo->image && file_exists(storage_path('app/public/' . $this->memo->image))) {
            Log::info('Adding image attachment to email', [
                'memo_id' => $this->memo->id,
                'image_path' => $this->memo->image,
                'recipient_email' => $this->subscriberEmail ?? 'Not specified'
            ]);

            $attachments[] = Attachment::fromPath(
                storage_path('app/public/' . $this->memo->image)
            )->withMime(mime_content_type(storage_path('app/public/' . $this->memo->image)));
        } else {
            Log::info('No image to attach or image file not found', [
                'memo_id' => $this->memo->id,
                'image_path' => $this->memo->image ?? 'null',
                'recipient_email' => $this->subscriberEmail ?? 'Not specified'
            ]);
        }

        return $attachments;
    }
}
