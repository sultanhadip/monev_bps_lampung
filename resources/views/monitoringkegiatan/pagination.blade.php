<nav>
    <ul class="pagination m-0">
        <li class="page-item {{ $monitoringKegiatan->onFirstPage() ? 'disabled' : '' }}">
            <a class="page-link" href="#" data-page="{{ $monitoringKegiatan->currentPage() - 1 }}">Previous</a>
        </li>
        @foreach ($monitoringKegiatan->getUrlRange(1, $monitoringKegiatan->lastPage()) as $page => $url)
        <li class="page-item {{ $monitoringKegiatan->currentPage() == $page ? 'active' : '' }}">
            <a class="page-link" href="#" data-page="{{ $page }}">{{ $page }}</a>
        </li>
        @endforeach
        <li class="page-item {{ !$monitoringKegiatan->hasMorePages() ? 'disabled' : '' }}">
            <a class="page-link" href="#" data-page="{{ $monitoringKegiatan->currentPage() + 1 }}">Next</a>
        </li>
    </ul>
</nav>