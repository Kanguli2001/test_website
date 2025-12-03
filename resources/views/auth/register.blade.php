<x-layout>
    <x-slot name="title">Create Account</x-slot>

    <div>
        <div class="hero min-h-[calc(100vh-16rem)]">
            <div class="hero-content flex-col">
                <div class="card w-96 bg-base-100">
                    <div class="card-body">
                        <h1 class="text-3xl font-bold text-center mb-6">
                            Create Account
                        </h1>

                        <form method="POST" action="/register">
                            @csrf

                            <!-- Name -->

                            <label class="floating-label mb-6">
                                <input type="text" 
                                name="name" 
                                placeholder="John Doe" 
                                value="{{ old('name') }}" 
                                class="input input-bordered @error('name') input-error @enderror w-full" 
                                required 
                                autofocus 
                                />

                                <span>Name</span>
                            </label>

                            @error('name')

                                <div class="label mt-4 mb-2">
                                    <span class="label-text-alt text-error">
                                        {{ $message }}
                                    </span>
                                </div>

                            @enderror

                            <!-- Email Address -->

                            <label class="floating-label mb-6">
                                <input type="text" 
                                name="email" 
                                placeholder="JohnDoe@example.com" 
                                value="{{ old('email') }}" 
                                class="input input-bordered @error('email') input-error @enderror w-full" 
                                required 
                                autofocus 
                                />

                                <span>Email</span>
                            </label>

                            @error('email')

                                <div class="label mt-4 mb-2">
                                    <span class="label-text-alt text-error">
                                        {{ $message }}
                                    </span>
                                </div>

                            @enderror

                            <!-- Password -->

                            <label class="floating-label mb-6">
                                <input type="password"
                                name="password"
                                placeholder="********"
                                class="input input-bordered @error('password') input-error @enderror w-full"
                                required
                                />

                                <span>Password</span>
                            </label>

                            @error('password')

                                <div class="label mt-4 mb-2">
                                    <span class="label-text-alt text-error">
                                        {{ $message }}
                                    </span>
                                </div>

                            @enderror

                            <label class="floating-label mb-6">
                                <input type="password"
                                name="password_confirmation"
                                placeholder="********"
                                class="input input-bordered"
                                required
                                />

                                <span>Confirm Password</span>
                            </label>

                            <!-- Submit Button -->

                            <div class="form-control mt-8">
                                <button class="btn btn-primary btn-sm" type="submit">
                                    Register
                                </button>
                            </div>

                        </form>

                        <div class="divider">OR</div>

                        <p class="text-center text-sm">
                            Already have an account?
                            <a href="/login" class="link link-primary">Sign In</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>



</x-layout>