<x-layouts.auth title="Potvrdi email" :subtitle="'Poslali smo link za potvrdu na '.auth()->user()->email.'. Otvori ga da nastaviš.'">
    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
        @csrf
        <button class="btn btn-primary w-full !h-12">Pošalji link ponovo</button>
    </form>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn w-full !h-12">Odjavi se</button>
    </form>
</x-layouts.auth>
