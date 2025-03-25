<?php

namespace App\Observers;

use App\Jobs\SendMemoPublishedEmails;
use App\Models\Memo;
use Illuminate\Support\Facades\Log;

class MemoObserver
{
    /**
     * Handle the Memo "created" event.
     */
    public function created(Memo $memo): void
    {
        Log::info('Memo created', [
            'memo_id' => $memo->id,
            'title' => $memo->title
        ]);

        // If the memo is published, send notification emails
        if ($memo->is_published) {
            Log::info('Dispatching memo emails for new published memo', [
                'memo_id' => $memo->id
            ]);
            SendMemoPublishedEmails::dispatch($memo);
        }
    }

    /**
     * Handle the Memo "updated" event.
     */
    public function updated(Memo $memo): void
    {
        Log::info('Memo updated', [
            'memo_id' => $memo->id,
            'title' => $memo->title
        ]);

        // Check if the memo was just published
        if ($memo->is_published && $memo->wasChanged('is_published')) {
            Log::info('Dispatching memo emails for newly published memo', [
                'memo_id' => $memo->id
            ]);
            SendMemoPublishedEmails::dispatch($memo);
        }
    }
}
