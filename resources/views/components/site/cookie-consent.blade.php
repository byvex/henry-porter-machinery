@php
    $hasConsent = request()->hasCookie('hpm_cookie_consent');
@endphp
<aside
    id="cookie-consent-banner"
    class="cookie-banner fixed bottom-4 left-4 right-4 z-50 sm:bottom-6 sm:left-auto sm:right-6 sm:max-w-md {{ $hasConsent ? 'is-hidden' : '' }}"
    style="{{ $hasConsent ? 'display: none;' : '' }}"
    role="dialog"
    aria-labelledby="cookie-consent-title"
    aria-describedby="cookie-consent-desc"
    aria-live="polite"
    data-cookie-banner
>
    <div class="rounded-2xl border border-stone-200/90 bg-white p-5 shadow-2xl ring-1 ring-black/5 dark:border-stone-800">
        <div class="flex items-start gap-3.5">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand/10 text-brand">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5" />
                    <path d="M8.5 8.5v.01" />
                    <path d="M16 15.5v.01" />
                    <path d="M12 12v.01" />
                    <path d="M11 17v.01" />
                    <path d="M7 13v.01" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 id="cookie-consent-title" class="font-display text-sm font-bold text-stone-900">
                    Cookie preferences
                </h3>
                <p id="cookie-consent-desc" class="mt-1 text-xs leading-relaxed text-stone-600 sm:text-[13px]">
                    We use essential cookies to ensure our site works properly. With your consent, we may also use optional cookies to improve your browsing experience. Read our <a href="{{ route('cookie-policy') }}" class="font-medium text-brand underline decoration-brand/30 underline-offset-2 hover:decoration-brand">Cookie Policy</a> to learn more.
                </p>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex cursor-pointer items-center justify-center rounded-full bg-brand px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand"
                        data-cookie-consent="accept"
                    >
                        Accept all
                    </button>
                    <button
                        type="button"
                        class="inline-flex cursor-pointer items-center justify-center rounded-full border border-stone-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-stone-700 shadow-xs transition hover:bg-stone-50 hover:text-stone-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-500"
                        data-cookie-consent="reject"
                    >
                        Reject non-essential
                    </button>
                </div>
            </div>
        </div>
    </div>
</aside>
