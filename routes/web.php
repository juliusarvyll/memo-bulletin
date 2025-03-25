<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


// Dashboard for authenticated users
Route::get('/', function () {
    return Inertia::render('Dashboard', [
        'memos' => App\Models\Memo::with(['category', 'author:id,name,avatar'])
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->get(),
    ]);
})->name('dashboard');

// Authenticated routes
Route::get('/memos', [DashboardController::class, 'memos'])->name('memos.index');
Route::get('/memos/{memo}', [DashboardController::class, 'show'])->name('memos.show');

// Include auth routes
require __DIR__.'/auth.php';


// // Test route for sending emails to ALL users
// Route::get('/test-all-users-email', function () {
//     $memo = \App\Models\Memo::latest()->first();

//     if (!$memo) {
//         return 'No memo found for testing.';
//     }

//     try {
//         Log::info('Starting test email to ALL users');

//         // Get all users
//         $users = \App\Models\User::all();

//         Log::info('Found users to notify', [
//             'count' => $users->count()
//         ]);

//         $sentCount = 0;
//         $errorCount = 0;
//         $skippedCount = 0;

//         foreach ($users as $user) {
//             // Skip if user has no email
//             if (empty($user->email)) {
//                 Log::warning('Skipping user - no email address', [
//                     'user_id' => $user->id,
//                     'user_name' => $user->name
//                 ]);
//                 $skippedCount++;
//                 continue;
//             }

//             try {
//                 Log::info('Sending test email to user', [
//                     'user_id' => $user->id,
//                     'email' => $user->email
//                 ]);

//                 \Illuminate\Support\Facades\Mail::to($user->email)
//                     ->send(new \App\Mail\MemoPublished($memo));

//                 Log::info('Successfully sent test email to user', [
//                     'email' => $user->email
//                 ]);

//                 $sentCount++;

//                 // Optional: Add some delay between emails
//                 if ($users->count() > 10) {
//                     usleep(200000); // 0.2 seconds
//                 }
//             } catch (\Exception $e) {
//                 $errorCount++;
//                 Log::error('Failed to send test email to user', [
//                     'email' => $user->email,
//                     'error' => $e->getMessage()
//                 ]);
//             }
//         }

//         return "Test complete. Emails sent to $sentCount users, skipped $skippedCount, errors $errorCount. Check logs for details.";
//     } catch (\Exception $e) {
//         Log::error('Error in test-all-users-email route', [
//             'error' => $e->getMessage(),
//             'trace' => $e->getTraceAsString()
//         ]);

//         return 'Error testing all-users email: ' . $e->getMessage();
//     }
// })->middleware(['auth']);

// // Test route for sending emails to a limited number of users
// Route::get('/test-limited-users-email/{limit?}', function ($limit = 3) {
//     $memo = \App\Models\Memo::latest()->first();

//     if (!$memo) {
//         return 'No memo found for testing.';
//     }

//     try {
//         Log::info('Starting test email to limited users');

//         // Get limited number of users
//         $users = \App\Models\User::take($limit)->get();

//         Log::info('Found users to notify', [
//             'count' => $users->count(),
//             'limit' => $limit
//         ]);

//         $emails = [];

//         foreach ($users as $user) {
//             if (!empty($user->email)) {
//                 \Illuminate\Support\Facades\Mail::to($user->email)
//                     ->send(new \App\Mail\MemoPublished($memo));

//                 $emails[] = $user->email;
//                 Log::info('Sent test email to: ' . $user->email);
//             }
//         }

//         return "Test emails sent to: " . implode(', ', $emails);
//     } catch (\Exception $e) {
//         Log::error('Test limited users email failed', [
//             'error' => $e->getMessage()
//         ]);

//         return 'Error: ' . $e->getMessage();
//     }
// })->middleware(['auth']);

// // Route to check user emails
// Route::get('/check-user-emails', function () {
//     $users = \App\Models\User::all(['id', 'name', 'email']);

//     $invalidDomains = ['example.com', 'example.net', 'example.org', 'test.com', 'localhost.com', 'invalid.com'];

//     $problematicEmails = [];
//     foreach ($users as $user) {
//         if (!$user->email) continue;

//         $domain = explode('@', $user->email)[1] ?? '';

//         if (in_array($domain, $invalidDomains) || str_contains($domain, 'example') || str_contains($domain, 'test')) {
//             $problematicEmails[] = [
//                 'id' => $user->id,
//                 'name' => $user->name,
//                 'email' => $user->email,
//                 'issue' => 'Reserved domain by RFC 2606'
//             ];
//         }
//     }

//     return [
//         'total_users' => $users->count(),
//         'problematic_emails' => $problematicEmails,
//         'count_problematic' => count($problematicEmails)
//     ];
// })->middleware(['auth']);

// Test route that filters out RFC 2606 reserved domains
Route::get('/test-valid-emails-only', function () {
    $memo = \App\Models\Memo::latest()->first();

    if (!$memo) {
        return 'No memo found for testing.';
    }

    try {
        // Get all users
        $users = \App\Models\User::all();

        $invalidDomains = ['example.com', 'example.net', 'example.org', 'test.com', 'localhost.com', 'invalid.com'];

        $sentCount = 0;
        $skippedCount = 0;
        $validEmails = [];

        foreach ($users as $user) {
            if (empty($user->email)) {
                $skippedCount++;
                continue;
            }

            // Check if domain is invalid
            $domain = explode('@', $user->email)[1] ?? '';
            if (in_array($domain, $invalidDomains) || str_contains($domain, 'example') || str_contains($domain, 'test')) {
                Log::warning('Skipping RFC 2606 reserved domain email', [
                    'email' => $user->email
                ]);
                $skippedCount++;
                continue;
            }

            // Send to valid emails only
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\MemoPublished($memo));

            $validEmails[] = $user->email;
            $sentCount++;
        }

        return "Emails sent to $sentCount valid addresses. Skipped $skippedCount invalid addresses. Valid emails: " . implode(', ', $validEmails);
    } catch (\Exception $e) {
        Log::error('Failed to send filtered emails', [
            'error' => $e->getMessage()
        ]);

        return 'Error: ' . $e->getMessage();
    }
})->middleware(['auth']);

// Debug routes for subscriber email notifications
Route::prefix('debug-emails')->middleware(['auth'])->group(function () {

    // Test route 1: Direct email sending to a specific address
    Route::get('/test-direct/{email?}', function (?string $email = null) {
        $memo = \App\Models\Memo::latest()->first();

        if (!$memo) {
            return 'No memo found for testing.';
        }

        $email = $email ?? auth()->user()->email;

        try {
            Log::info('Testing direct email sending', [
                'memo_id' => $memo->id,
                'email' => $email
            ]);

            Mail::to($email)->send(new \App\Mail\MemoPublished($memo, $email));

            return "Email sent directly to $email. Check your inbox.";
        } catch (\Exception $e) {
            Log::error('Direct email test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return "Error sending email: " . $e->getMessage();
        }
    });

    // Test route 2: Test the job dispatch and execution
    Route::get('/test-job', function () {
        $memo = \App\Models\Memo::latest()->first();

        if (!$memo) {
            return 'No memo found for testing.';
        }

        try {
            Log::info('Dispatching SendMemoPublishedEmails job for testing');
            \App\Jobs\SendMemoPublishedEmails::dispatch($memo);

            return "Job dispatched. Check logs for details.";
        } catch (\Exception $e) {
            Log::error('Job dispatch test failed', [
                'error' => $e->getMessage()
            ]);

            return "Error dispatching job: " . $e->getMessage();
        }
    });

    // Test route 3: Check subscriber filtering and log potential recipients
    Route::get('/check-subscribers', function () {
        $subscribers = \App\Models\SubscriberEmail::where('is_active', true)
            ->whereNotNull('verified_at')
            ->get();

        $subscriberDetails = $subscribers->map(function ($subscriber) {
            return [
                'id' => $subscriber->id,
                'email' => $subscriber->email,
                'name' => $subscriber->name,
                'verified_at' => $subscriber->verified_at->format('Y-m-d H:i:s'),
                'created_at' => $subscriber->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return [
            'total_active_verified_subscribers' => $subscribers->count(),
            'subscribers' => $subscriberDetails
        ];
    });

    // Test route 4: Debug MemoPublished mailable
    Route::get('/debug-mailable', function () {
        $memo = \App\Models\Memo::latest()->first();

        if (!$memo) {
            return 'No memo found for testing.';
        }

        try {
            // Explicitly setting debug to true in the environment for this request
            app()->detectEnvironment(function() { return 'local'; });

            // Force debug mode
            config(['app.debug' => true]);

            // Get the rendered mailable content for inspection
            $mailable = new \App\Mail\MemoPublished($memo, 'test@example.com');
            $renderedMailable = $mailable->render();

            return [
                'memo_id' => $memo->id,
                'memo_title' => $memo->title,
                'mail_from' => config('mail.from.address'),
                'mail_from_name' => config('mail.from.name'),
                'mail_class_parameters' => [
                    'memo' => get_class($memo),
                    'email_param' => 'test@example.com'
                ],
                'mail_content_preview' => substr($renderedMailable, 0, 500) . '...',
                'config' => [
                    'mail_driver' => config('mail.default'),
                    'queue_connection' => config('queue.default'),
                ],
                'hints' => [
                    'check_env_file' => 'Verify MAIL_* settings in .env file',
                    'check_mail_templates' => 'Verify blade templates in resources/views/emails/',
                    'check_mailable_constructor' => 'Verify MemoPublished constructor accepts correct parameters',
                ]
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
        }
    });

    // Test route 5: Force send emails to all verified subscribers
    Route::get('/force-send-to-subscribers', function () {
        $memo = \App\Models\Memo::latest()->first();

        if (!$memo) {
            return 'No memo found for testing.';
        }

        try {
            // Only filter by active status, not verification status
            $subscribers = \App\Models\SubscriberEmail::where('is_active', true)->get();

            Log::info('Force sending emails to all active subscribers', [
                'memo_id' => $memo->id,
                'subscriber_count' => $subscribers->count()
            ]);

            $sentCount = 0;
            $errorCount = 0;

            foreach ($subscribers as $subscriber) {
                try {
                    Mail::to($subscriber->email)
                        ->send(new \App\Mail\MemoPublished($memo, $subscriber->email));

                    Log::info('Force sent email to subscriber', [
                        'email' => $subscriber->email
                    ]);

                    $sentCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error('Failed to force send email', [
                        'email' => $subscriber->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return "Force sent emails to $sentCount subscribers. Errors: $errorCount. Check logs for details.";
        } catch (\Exception $e) {
            Log::error('Force send test failed', [
                'error' => $e->getMessage()
            ]);

            return "Error in force send test: " . $e->getMessage();
        }
    });

    // Debug route to check exactly which subscribers are eligible
    Route::get('/eligible-subscribers', function () {
        // Get all subscribers
        $allSubscribers = \App\Models\SubscriberEmail::all();

        // Get active subscribers
        $activeSubscribers = \App\Models\SubscriberEmail::active()->get();

        // Get verified subscribers
        $verifiedSubscribers = \App\Models\SubscriberEmail::verified()->get();

        // Get both active and verified subscribers
        $oldEligibleSubscribers = \App\Models\SubscriberEmail::active()->verified()->get();

        // Get new eligible subscribers (active only)
        $newEligibleSubscribers = \App\Models\SubscriberEmail::active()->get();

        // Get details of new eligible subscribers
        $eligibleDetails = $newEligibleSubscribers->map(function ($sub) {
            return [
                'id' => $sub->id,
                'email' => $sub->email,
                'name' => $sub->name,
                'is_active' => $sub->is_active,
                'verified_at' => $sub->verified_at ? $sub->verified_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return [
            'all_subscribers_count' => $allSubscribers->count(),
            'active_subscribers_count' => $activeSubscribers->count(),
            'verified_subscribers_count' => $verifiedSubscribers->count(),
            'old_eligible_subscribers_count' => $oldEligibleSubscribers->count(),
            'new_eligible_subscribers_count' => $newEligibleSubscribers->count(),
            'eligible_subscribers' => $eligibleDetails,
        ];
    });

    // Route to fix existing subscriber emails by updating domains
    Route::get('/fix-subscriber-domains', function () {
        $beforeCount = \App\Models\SubscriberEmail::count();
        $updatedCount = 0;
        $realDomains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com', 'aol.com'];

        // Get all subscribers
        $subscribers = \App\Models\SubscriberEmail::all();

        foreach ($subscribers as $subscriber) {
            $parts = explode('@', $subscriber->email);
            $username = $parts[0];
            $domain = $parts[1] ?? '';

            // If domain contains example or test, replace it
            if (str_contains($domain, 'example') || str_contains($domain, 'test')) {
                $newDomain = $realDomains[array_rand($realDomains)];
                $newEmail = $username . '@' . $newDomain;

                // Update the subscriber
                $subscriber->update(['email' => $newEmail]);
                $updatedCount++;
            }
        }

        return "Updated $updatedCount of $beforeCount subscriber email domains.";
    });
});


