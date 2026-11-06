<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyEnquiry;
use App\Models\Enquiry;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Throwable;

class EnquiryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255'], 'phone' => ['required', 'string', 'max:50'], 'message' => ['required', 'string', 'min:10', 'max:10000'], 'product_id' => ['nullable', 'uuid'], 'submission_token' => ['required', 'uuid'], 'website' => ['nullable', 'string', 'max:255']]);
        $key = hash('sha256', $data['submission_token']);
        $product = empty($data['product_id']) ? null : Product::catalogue()->whereKey($data['product_id'])->first();
        if (! empty($data['product_id']) && ! $product) {
            throw ValidationException::withMessages(['product_id' => 'This machine is no longer listed. Please send a general enquiry.']);
        }
        $destination = $product ? route('machinery.show', $product->slug).'#enquiry' : route('contact').'#enquiry';
        if (Enquiry::where('submission_key', $key)->exists()) {
            return redirect($destination)->with('enquiry_success', true);
        }
        if (! in_array($data['submission_token'], $request->session()->get('enquiry_tokens', []), true)) {
            throw ValidationException::withMessages(['message' => 'Please refresh the page and try again.']);
        }
        if (! empty($data['website'])) {
            throw ValidationException::withMessages(['message' => 'Unable to submit this enquiry.']);
        }
        if (RateLimiter::tooManyAttempts('enquiry:'.$request->ip(), 5)) {
            throw ValidationException::withMessages(['message' => 'Please wait before sending another enquiry, or contact us by phone.']);
        }
        RateLimiter::hit('enquiry:'.$request->ip(), 600);
        $enquiry = Enquiry::firstOrCreate(['submission_key' => $key], ['name' => trim($data['name']), 'email' => $data['email'], 'phone' => trim($data['phone']), 'message' => strip_tags($data['message']), 'product_id' => $product?->id, 'product_name' => $product?->name, 'product_url' => $product ? rtrim(config('app.url'), '/').route('machinery.show', $product->slug, false) : null, 'notification_status' => config('enquiries.notifications_enabled') ? 'pending' : 'disabled']);
        if ($enquiry->wasRecentlyCreated && config('enquiries.notifications_enabled')) {
            try {
                NotifyEnquiry::dispatch($enquiry->id)->afterCommit();
            } catch (Throwable $e) {
                report($e);
                $enquiry->update(['notification_status' => 'failed']);
            }
        }

        return redirect($destination)->with('enquiry_success', true);
    }
}
