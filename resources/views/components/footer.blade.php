<div class="footer container">
    <div class="grid grid-cols-3 py-8">
        <div>&nbsp;</div>
        <div class="w-11/12">
            &copy; {{ date('Y') }} <a href="{{ url('/') }}">by {{ config('app.author') }}</a>
        </div>
        <div class="text-right">
            <a href="javascript:window.scrollTo({top: 0, behavior: 'smooth'});" class="text-white">
                <img src="{{ asset('images/arrow-up.svg') }}" alt="Nach oben" class="inline-block w-4 h-4">
            </a>
        </div>
    </div>
</div>
