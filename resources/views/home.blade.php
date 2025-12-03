
<x-layout>
    <x-slot:title>
        Home Feed
    </x-slot:title>

    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mt-8">Latest Chirps</h1>

        <!-- Chirp Form - Only for logged-in users -->
        @auth
        <div class="card bg-base-100 shadow mt-8">
            <div class="card-body">
                <form method="POST" action="/chirps">
                    @csrf
                    <div class="form-control w-full">
                        <textarea name="message" placeholder="what's on your mind?" class="textarea textarea-bordered w-full resize" rows="4" maxlength="255" required id=""> {{ old('message') }}</textarea>

                        @error('message')
                            <div class="label">
                                <span class="label-text-alt -mt-pxtext-error">
                                    {{ $message }}
                                </span>
                            </div>
                        @enderror
                    </div>

                    <div class="mt-4 flex items-center justify-end">
                        <button type="submit" class="btn btn-primary btn-sm">
                            Send Chirp
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="card bg-base-100 shadow mt-8">
            <div class="card-body text-center">
                <p class="text-lg">Sign in to share your thoughts and chirp with the community!</p>
                <div class="mt-4 flex gap-2 justify-center">
                    <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-ghost">Register</a>
                </div>
            </div>
        </div>
        @endauth


        <div class="space-y-4 mt-8">
            @forelse ($chirps as $chirp)
                <x-chirp :chirp="$chirp" />
            @empty
                <div class="hero py-12">
                    <div class="hero-content text-center">
                        <div>
                            <svg class="mx-auto h-12 w-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <p class="mt-4 text-base-content/60">No chirps yet. Be the first to chirp!</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</x-layout>