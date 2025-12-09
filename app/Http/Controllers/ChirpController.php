<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chirp;

class ChirpController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function index(Request $request)
    {
        $chirps = Chirp::with('user')->latest()->take(50)->get();
        
        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json($chirps, 200);
        }
        
        return view('home', ['chirps' => $chirps]);
    }

    public function store(Request $request)
    {
        //Validate the request
        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ], [
            'message.required'=> 'Please write a message before submitting.',
            'message.max'=> 'Your message is too long. Maximum length is 255 characters.',
        ]);

        //Create a new chirp
        $chirp = $request->user()->chirps()->create([
            'message' => $validated['message'],
        ]);

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json($chirp, 201);
        }

        //Redirect back to the homepage
        return redirect('/')->with('status', 'Chirp created successfully!');

    }

    public function edit (Chirp $chirp)
    {

        $this->authorize('update', $chirp);

        return view('chirps.edit', compact('chirp'));
    }

    public function update(Request $request, Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ], [
            'message.required'=> 'Please write a message before submitting.',
            'message.max'=> 'Your message is too long. Maximum length is 255 characters.',
        ]);

        $chirp->update([
            'message' => $validated['message'],
        ]);

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json($chirp, 200);
        }

        return redirect('/')->with('status', 'Chirp updated successfully!');
    }

    public function destroy(Request $request, Chirp $chirp)
    {
        $this->authorize('delete', $chirp);
        $chirp->delete();

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Chirp deleted successfully'], 204);
        }

        return redirect('/')->with('success', 'Chirp deleted successfully!');
    }


}
