<?php

namespace App\Http\Controllers;

use App\Mail\PropertyInquiryMail;
use App\Models\PropertyInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InquiryController extends Controller
{
    public function store(Request $request)
    {
        // Honeypot: bots fill hidden fields, real visitors never see this input.
        if ($request->filled('website')) {
            return response()->json(['success' => true]);
        }

        $validated = $request->validate([
            'full_name'          => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255'],
            'phone'               => ['nullable', 'string', 'max:30'],
            'property_interest'   => ['nullable', 'string', 'max:255'],
            'message'             => ['nullable', 'string', 'max:2000'],
        ]);

        $inquiry = PropertyInquiry::create([
            'full_name'         => $validated['full_name'],
            'email'             => $validated['email'],
            'phone'             => $validated['phone'] ?? null,
            'property_interest' => $validated['property_interest'] ?? null,
            'message'           => $validated['message'] ?? null,
            'source'            => 'landing_page',
            'ip_address'        => $request->ip(),
        ]);

        $recipient = config('mail.arkcrest_inquiry_to') ?: config('mail.from.address');

        try {
            Mail::to($recipient)->send(new PropertyInquiryMail($inquiry));
            $inquiry->update(['email_sent' => true]);
        } catch (\Throwable $e) {
            // Never fail the client's submission just because mail delivery had an issue —
            // the inquiry is already safely stored in the database either way.
            Log::error('Property inquiry email failed to send: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your inquiry has been received. Our team will reach out shortly.',
        ]);
    }
}