{{-- Language Switcher Component --}}
<div class="relative inline-block text-left">
    <div>
        <button type="button" 
                class="inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" 
                id="language-menu-button" 
                aria-expanded="true" 
                aria-haspopup="true"
                onclick="toggleLanguageDropdown()">
            {{ language_flag(app()->getLocale()) }}
            {{ get_available_languages()[app()->getLocale()] ?? __('common.language') }}
            <svg class="-mr-1 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <div class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none hidden" 
         role="menu" 
         aria-orientation="vertical" 
         aria-labelledby="language-menu-button" 
         tabindex="-1"
         id="language-dropdown">
        <div class="py-1" role="none">
            @foreach(get_available_languages() as $code => $name)
                <a href="{{ route('language.switch', $code) }}" 
                   class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100 hover:text-gray-900 {{ app()->getLocale() === $code ? 'bg-gray-50 text-gray-900' : '' }}" 
                   role="menuitem" 
                   tabindex="-1">
                    {{ language_flag($code) }} {{ $name }}
                    @if(app()->getLocale() === $code)
                        <span class="ml-2 text-green-600">✓</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>

<script>
function toggleLanguageDropdown() {
    const dropdown = document.getElementById('language-dropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const button = document.getElementById('language-menu-button');
    const dropdown = document.getElementById('language-dropdown');
    
    if (!button.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.add('hidden');
    }
});
</script>
