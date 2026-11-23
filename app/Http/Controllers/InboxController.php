<?php

namespace App\Http\Controllers;

use App\Models\ConciergeRequest;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use App\Services\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InboxController extends Controller
{
    public function contact(): View
    {
        return view('pages.contact');
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:160'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:8', 'max:3000'],
        ]);

        ContactMessage::create($data);

        return redirect()->route('contact')
            ->with('status', 'Thanks for reaching out! Our team has received your message and will reply soon.');
    }

    public function concierge(): View
    {
        return view('pages.concierge');
    }

    public function storeConcierge(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:160'],
            'product_name' => ['required', 'string', 'max:200'],
            'brand' => ['nullable', 'string', 'max:120'],
            'reference_url' => ['nullable', 'url', 'max:500'],
            'budget' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $stored = $request->file('image')->store('images/uploads/concierge', 'public_root');
            $imagePath = ImageOptimizer::toWebp($stored);
        }

        ConciergeRequest::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'product_name' => $data['product_name'],
            'brand' => $data['brand'] ?? null,
            'reference_url' => $data['reference_url'] ?? null,
            'budget' => $data['budget'] ?? null,
            'notes' => $data['notes'] ?? null,
            'image' => $imagePath,
            'status' => 'new',
        ]);

        return redirect()->route('concierge')
            ->with('status', 'Request received! Our sourcing team will check authenticity & pricing and call or message you soon.');
    }

    public function newsletter(): View
    {
        return view('pages.newsletter');
    }

    public function storeNewsletter(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:160'],
        ]);

        $email = strtolower(trim($data['email']));
        $existing = NewsletterSubscriber::where('email', $email)->first();

        if ($existing) {
            if (! $existing->is_active) {
                $existing->update(['is_active' => true, 'subscribed_at' => now()]);
            }
            $message = 'You are already subscribed to SafeBuy updates!';
        } else {
            NewsletterSubscriber::create([
                'email' => $email,
                'is_active' => true,
                'subscribed_at' => now(),
            ]);
            $message = 'Thanks! You are now subscribed to SafeBuy deals & authenticity alerts.';
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return back()->with('newsletter_status', $message);
    }
}
