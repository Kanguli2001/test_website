<x-layout>
    <x-slot name="title">Verify Email</x-slot>

    <div class="hero min-h-[calc(100vh-16rem)]">
        <div class="hero-content flex-col">
            <div class="card w-96 bg-base-100">
                <div class="card-body">
                    <h1 class="text-3xl font-bold text-center mb-6">
                        Verify Your Email
                    </h1>

                    <p class="text-center mb-6">
                        Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
                    </p>

                    @if (session('resent'))
                        <div class="alert alert-success mb-4">
                            A fresh verification link has been sent to your email address.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full">
                            Resend Verification Email
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-ghost w-full">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-layout>
