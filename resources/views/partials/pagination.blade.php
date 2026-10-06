@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="custom-pagination-nav">
        {{-- Tombol Halaman Sebelumnya --}}
        @if ($paginator->onFirstPage())
            <span class="pagination-btn disabled" aria-disabled="true">
                <i class="fa-solid fa-chevron-left"></i> Sebelumnya
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-btn" aria-label="Halaman Sebelumnya">
                <i class="fa-solid fa-chevron-left"></i> Sebelumnya
            </a>
        @endif

        {{-- Nomor-nomor Halaman --}}
        <div class="pagination-pages">
            @foreach ($elements as $element)
                {{-- Pemisah Tiga Titik (...) --}}
                @if (is_string($element))
                    <span class="pagination-number dots">{{ $element }}</span>
                @endif

                {{-- Daftar Nomor Halaman --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-number active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-number">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Tombol Halaman Selanjutnya --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-btn" aria-label="Halaman Selanjutnya">
                Selanjutnya <i class="fa-solid fa-chevron-right"></i>
            </a>
        @else
            <span class="pagination-btn disabled" aria-disabled="true">
                Selanjutnya <i class="fa-solid fa-chevron-right"></i>
            </span>
        @endif
    </nav>
@endif
