{{-- Unified Language Switcher Component - Landing Page Style --}}
{{-- Usage: @include('partials.language-switcher') --}}

@php
    $currentLocale = app()->getLocale();
    $supportedLocales = \App\Http\Controllers\LanguageController::getSupportedLocales();
@endphp

<div class="language-switcher" dir="ltr" style="direction: ltr !important;">
    {{-- Language Toggle Button --}}
    <button
        type="button"
        class="language-toggle"
        onclick="toggleUnifiedLanguageDropdown()"
        aria-label="{{ __('messages.language') }}"
        aria-haspopup="true"
        aria-expanded="false"
    >
        <span style="opacity: 0.9;">🌐</span>
        <span class="current-locale">
            @if($currentLocale === 'ar')
                ع
            @else
                EN
            @endif
        </span>
        <span class="lang-divider" style="opacity: 0.5;">|</span>
        <span class="lang-alt" style="opacity: 0.7;">
            @if($currentLocale === 'ar')
                EN
            @else
                ع
            @endif
        </span>
        <svg style="width: 12px; height: 12px; transition: transform 0.2s;" id="lang-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    {{-- Dropdown Menu --}}
    <div
        id="unified-language-dropdown"
        class="language-dropdown"
    >
        <div class="language-header">
            <span>{{ __('messages.select_language') }}</span>
        </div>
        
        @foreach($supportedLocales as $locale => $details)
            <a
                href="#"
                onclick="switchUnifiedLanguage('{{ $locale }}'); return false;"
                class="language-option {{ $currentLocale === $locale ? 'active' : '' }}"
                data-locale="{{ $locale }}"
            >
                <span style="font-size: 18px;">{{ $details['flag'] }}</span>
                <div style="display: flex; flex-direction: column;">
                    <span style="font-size: 14px; font-weight: 500;">{{ $details['native_name'] }}</span>
                    @if($details['name'] !== $details['native_name'])
                        <span style="font-size: 11px; opacity: 0.6;">{{ $details['name'] }}</span>
                    @endif
                </div>
                @if($currentLocale === $locale)
                    <svg style="width: 16px; height: 16px; margin-left: auto;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </a>
        @endforeach
    </div>
</div>

<script>
function toggleUnifiedLanguageDropdown() {
    const dropdown = document.getElementById('unified-language-dropdown');
    const arrow = document.getElementById('lang-arrow');
    
    if (!dropdown) return;
    
    const isVisible = dropdown.classList.contains('show');
    
    if (isVisible) {
        dropdown.classList.remove('show');
        if (arrow) arrow.style.transform = 'rotate(0deg)';
    } else {
        dropdown.classList.add('show');
        if (arrow) arrow.style.transform = 'rotate(180deg)';
    }
}

function switchUnifiedLanguage(locale) {
    // Show loading overlay if exists
    const loading = document.getElementById('language-loading');
    if (loading) {
        loading.style.display = 'flex';
    }
    
    // Close dropdown
    toggleUnifiedLanguageDropdown();
    
    // Navigate to language switch route with current URL as return
    const currentPath = window.location.pathname + window.location.search;
    window.location.href = '/lang/' + locale + '?return=' + encodeURIComponent(currentPath);
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const switcher = document.querySelector('.language-switcher');
    if (switcher && !switcher.contains(event.target)) {
        const dropdown = document.getElementById('unified-language-dropdown');
        const arrow = document.getElementById('lang-arrow');
        
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }
});

// Close dropdown on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const dropdown = document.getElementById('unified-language-dropdown');
        const arrow = document.getElementById('lang-arrow');
        
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }
});
</script>
