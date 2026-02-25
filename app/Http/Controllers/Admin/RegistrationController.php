<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ClientRegistrationLink;
use App\Mail\RegistrationLinkMail;
use App\Models\RegistrationLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RegistrationController extends Controller
{
    // Admin page to send registration emails
    public function index()
    {
        return Inertia::render('admin/registration/index');
    }

    // Handle sending registration email
    public function sendLink(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $token = Str::random(40);

    $link = RegistrationLink::create([
        'email' => $request->email,
        'token' => $token,
        'status' => 'pending',
    ]);

    // Generate full registration URL
    $registrationUrl = route('client.registration.create', $token);

    // Send email
    Mail::to($request->email)->queue(new RegistrationLinkMail($registrationUrl));

    return back()->with('success', 'Registration email sent successfully');
}

    // Show client registration form
    public function create($token)
{
    $link = RegistrationLink::where('token', $token)
        ->where('status', 'pending')
        ->firstOrFail();

    return view('emails.registration.create', [
        'token' => $link->token,
        'email' => $link->email,
    ]);
}

    // Handle client registration form submission
  public function store(Request $request, $token)
{
    $link = RegistrationLink::where('token', $token)
        ->where('status', 'pending')
        ->firstOrFail();

    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'required|string|max:20',
        'address' => 'nullable|string|max:255',
    ]);

    // Save form submission to database
    \App\Models\ClientRegistration::create($data);

    // Mark link as completed
    $link->update(['status' => 'completed']);

    return redirect()->route('client.registration.thankyou')
                     ->with('success', 'Form submitted successfully!');
}
}