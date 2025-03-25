<?php

namespace App\Jobs;

use App\Mail\MemoPublished;
use App\Models\Memo;
use App\Models\SubscriberEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMemoPublishedEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The memo that was published.
     *
     * @var \App\Models\Memo
     */
    protected $memo;

    /**
     * Create a new job instance.
     */
    public function __construct(Memo $memo)
    {
        $this->memo = $memo;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting SendMemoPublishedEmails job', [
            'memo_id' => $this->memo->id,
            'memo_title' => $this->memo->title
        ]);

        // Only filter by active status, not verification status
        $subscribers = SubscriberEmail::active()->get();

        // Detailed subscriber logging to troubleshoot
        $subscriberDetails = $subscribers->map(function ($sub) {
            return [
                'id' => $sub->id,
                'email' => $sub->email,
                'name' => $sub->name,
                'is_active' => $sub->is_active,
                'verified_at' => $sub->verified_at ? $sub->verified_at->format('Y-m-d H:i:s') : null,
            ];
        });

        Log::info('Found subscribers to notify', [
            'subscriber_count' => $subscribers->count(),
            'subscriber_details' => $subscriberDetails
        ]);

        // Track statistics
        $sentCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        // Define invalid domains (RFC 2606 reserved)
        $invalidDomains = [
            'example.com', 'example.net', 'example.org',
            'test.com', 'localhost', 'invalid.com'
        ];

        // Loop through each subscriber and send the email
        foreach ($subscribers as $subscriber) {
            try {
                // Skip if email is empty
                if (empty($subscriber->email)) {
                    Log::warning('Skipping subscriber with empty email', [
                        'subscriber_id' => $subscriber->id
                    ]);
                    $skippedCount++;
                    continue;
                }

                // Check if domain is invalid
                $domain = explode('@', $subscriber->email)[1] ?? '';
                if (
                    in_array($domain, $invalidDomains) ||
                    str_contains($domain, 'example') ||
                    str_contains($domain, 'test')
                ) {
                    Log::warning('Skipping RFC 2606 reserved domain email', [
                        'email' => $subscriber->email,
                        'domain' => $domain
                    ]);
                    $skippedCount++;
                    continue;
                }

                Log::info('Sending memo publication email', [
                    'subscriber_id' => $subscriber->id,
                    'email' => $subscriber->email,
                    'memo_id' => $this->memo->id
                ]);

                Mail::to($subscriber->email)
                    ->send(new MemoPublished($this->memo, $subscriber->email));

                $sentCount++;

                // Add a small delay between emails to avoid rate limiting
                if ($subscribers->count() > 5) {
                    sleep(1);
                }
            } catch (\Exception $e) {
                $errorCount++;
                Log::error('Failed to send memo email', [
                    'subscriber_id' => $subscriber->id,
                    'email' => $subscriber->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        Log::info('Completed sending memo published emails', [
            'memo_id' => $this->memo->id,
            'sent_count' => $sentCount,
            'error_count' => $errorCount,
            'skipped_count' => $skippedCount,
            'total_subscribers' => $subscribers->count()
        ]);
    }
}
