<?php

namespace App\Jobs;

use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NotifyEnquiry implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public string $enquiryId) {}

    public function handle(): void
    {
        $enquiry = Enquiry::find($this->enquiryId);
        if (! $enquiry || $enquiry->notification_status === 'sent') {
            return;
        }
        Mail::to(config('enquiries.recipient'))->send(new EnquiryReceived($enquiry));
        $enquiry->update(['notification_status' => 'sent']);
    }

    public function failed(?Throwable $exception): void
    {
        Enquiry::whereKey($this->enquiryId)->update(['notification_status' => 'failed']);
    }
}
