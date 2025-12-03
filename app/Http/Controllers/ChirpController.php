<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chirp;

class ChirpController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function index()
    {
        $chirps = Chirp::with('user')->latest()->take(50)->get();
        
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
        $request->user()->chirps()->create([
            'message' => $validated['message'],
        ]);

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

        /* if ($request->user()->cannot('update', $chirp)) {
            abort(403);
        }  */
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

        return redirect('/')->with('status', 'Chirp updated successfully!');
    }

    public function destroy (Chirp $chirp)
    {

        $this->authorize('delete', $chirp);
        $chirp->delete();

        return redirect('/')->with('success', 'Chirp deleted successfully!');
    }


}
