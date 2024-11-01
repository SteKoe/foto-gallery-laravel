<div class="footer container">
    <div class="grid grid-cols-3 py-8">
        <div>&nbsp;</div>
        <div class="w-11/12">
            &copy; {{ date('Y') }} <a href="{{ url('/') }}">by {{ config('app.author') }}</a>
        </div>
        <div class="text-right">
            <a href="javascript:window.scrollTo({top: 0, behavior: 'smooth'});" id="to-top" class="opacity-0 transition-all text-white fixed bottom-5 right-5 rounded-full bg-black/60 backdrop-blur-2xl p-4">
                <img src="{{ asset('images/arrow-up.svg') }}" alt="Nach oben" class="inline-block w-4 h-4">
            </a>
        </div>
    </div>
</div>
