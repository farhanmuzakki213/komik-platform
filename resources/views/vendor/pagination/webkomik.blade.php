@if ($paginator->hasPages())
    <nav class="flex justify-center mt-8 mb-4">
        <ul class="flex items-center space-x-1 sm:space-x-2">
            
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li>
                        <span class="flex items-center justify-center w-8 h-8 text-gray-400 text-sm font-bold">
                            {{ $element }}
                        </span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li>
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-[#00dc64] text-white text-[13px] font-bold shadow-sm">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            <li>
                                <button wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" 
                                        class="flex items-center justify-center w-8 h-8 rounded-full text-gray-500 hover:bg-gray-100 hover:text-black text-[13px] font-bold transition-colors">
                                    {{ $page }}
                                </button>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

        </ul>
    </nav>
@endif