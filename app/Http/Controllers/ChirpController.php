<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\chirp;

class ChirpController extends Controller
{
    public function index()
    {
        $chirps = chirp::with('user')->latest()->take(50)->get();
        
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
        chirp::create([
            'message' => $validated['message'],
            'user_id' => null,
        ]);

        //Redirect back to the homepage
        return redirect('/')->with('status', 'Chirp created successfully!');

    }

    public function edit (chirp $chirp)
    {
        return view('chirps.edit', compact('chirp'));
    }

    public function update(Request $request, chirp $chirp)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ], [
            'message.required'=> 'Please write a message before submitting.',
            'message.max'=> 'Your message is too long. Maximum length is 255 characters.',
        ]);

        $chirp->update([
            'message' => $validated['message'],
        ]);

        return redirect('/')->with('status', 'Chirp updated successfully!');
    }

    public function destroy (chirp $chirp)
    {
        $chirp->delete();

        return redirect('/')->with('success', 'Chirp deleted successfully!');
    }


}
