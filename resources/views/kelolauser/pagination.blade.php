@php
$currentPage = $kelolauser->currentPage();
$lastPage = $kelolauser->lastPage();
$maxLinks = 5;
$query = request()->except('page');

function pageUrl($page, $query) {
return url()->current() . '?' . http_build_query(array_merge($query, ['page' => $page]));
}

$start = max(1, $currentPage - floor($maxLinks / 2));
$end = min($lastPage, $start + $maxLinks - 1);
if (($end - $start + 1) < $maxLinks) {
    $start=max(1, $end - $maxLinks + 1);
    }
    @endphp

    <ul class="pagination m-0">
    @if ($kelolauser->onFirstPage())
    <li class="page-item disabled"><span class="page-link">Previous</span></li>
    @else
    <li class="page-item">
        <a href="#" class="page-link" data-page="{{ $currentPage - 1 }}">Previous</a>
    </li>
    @endif

    @if ($start > 1)
    <li class="page-item {{ $currentPage == 1 ? 'active' : '' }}">
        <a href="#" class="page-link" data-page="1">1</a>
    </li>
    @if ($start > 2)
    <li class="page-item disabled"><span class="page-link">...</span></li>
    @endif
    @endif

    @for ($page = $start; $page <= $end; $page++)
        <li class="page-item {{ $page == $currentPage ? 'active' : '' }}">
        <a href="#" class="page-link" data-page="{{ $page }}">{{ $page }}</a>
        </li>
        @endfor

        @if ($end < $lastPage)
            @if ($end < $lastPage - 1)
            <li class="page-item disabled"><span class="page-link">...</span></li>
            @endif
            <li class="page-item {{ $currentPage == $lastPage ? 'active' : '' }}">
                <a href="#" class="page-link" data-page="{{ $lastPage }}">{{ $lastPage }}</a>
            </li>
            @endif

            @if ($kelolauser->hasMorePages())
            <li class="page-item">
                <a href="#" class="page-link" data-page="{{ $currentPage + 1 }}">Next</a>
            </li>
            @else
            <li class="page-item disabled"><span class="page-link">Next</span></li>
            @endif
            </ul>