<div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>

@once
    @push('scripts')
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endpush
@endonce