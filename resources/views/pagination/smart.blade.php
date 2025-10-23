@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center">
        <div class="flex items-center space-x-1">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-2 text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                    <i class="fas fa-chevron-left text-xs"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" 
                   class="px-3 py-2 text-gray-600 hover:text-blue-600 bg-white hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg transition-colors duration-200">
                    <i class="fas fa-chevron-left text-xs"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $showPages = [];
                
                if ($lastPage <= 7) {
                    // Show all pages if 7 or fewer
                    for ($i = 1; $i <= $lastPage; $i++) {
                        $showPages[] = $i;
                    }
                } else {
                    // Smart pagination logic
                    if ($currentPage <= 4) {
                        // Beginning: 1 2 3 4 5 ... Last
                        $showPages = [1, 2, 3, 4, 5, '...', $lastPage];
                    } elseif ($currentPage >= $lastPage - 3) {
                        // End: 1 ... (Last-4) (Last-3) (Last-2) (Last-1) Last
                        $showPages = [1, '...', $lastPage - 4, $lastPage - 3, $lastPage - 2, $lastPage - 1, $lastPage];
                    } else {
                        // Middle: 1 ... (Current-1) Current (Current+1) ... Last
                        $showPages = [1, '...', $currentPage - 1, $currentPage, $currentPage + 1, '...', $lastPage];
                    }
                }
            @endphp

            @foreach ($showPages as $page)
                @if ($page === '...')
                    <span class="px-3 py-2 text-gray-400 bg-gray-50 rounded-lg">
                        <i class="fas fa-ellipsis-h text-xs"></i>
                    </span>
                @elseif ($page == $currentPage)
                    <span class="px-3 py-2 text-white bg-blue-600 rounded-lg font-medium shadow-sm">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $paginator->url($page) }}" 
                       class="px-3 py-2 text-gray-600 hover:text-blue-600 bg-white hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg transition-colors duration-200">
                        {{ $page }}
                    </a>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" 
                   class="px-3 py-2 text-gray-600 hover:text-blue-600 bg-white hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg transition-colors duration-200">
                    <i class="fas fa-chevron-right text-xs"></i>
                </a>
            @else
                <span class="px-3 py-2 text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                    <i class="fas fa-chevron-right text-xs"></i>
                </span>
            @endif
        </div>
    </nav>
@endif