{{-- resources/views/dashboard/partials/pagination.blade.php --}}
@if ($paginator->hasPages())
<nav class="pagination" role="navigation">
  {{-- Previous --}}
  @if ($paginator->onFirstPage())
    <button class="pg-btn" disabled><i class="ri-arrow-left-s-line"></i></button>
  @else
    <a href="{{ $paginator->previousPageUrl() }}" class="pg-btn"><i class="ri-arrow-left-s-line"></i></a>
  @endif

  {{-- Page numbers --}}
  @foreach ($elements as $element)
    @if (is_string($element))
      <button class="pg-btn" disabled>{{ $element }}</button>
    @endif
    @if (is_array($element))
      @foreach ($element as $page => $url)
        @if ($page == $paginator->currentPage())
          <button class="pg-btn active">{{ $page }}</button>
        @else
          <a href="{{ $url }}" class="pg-btn">{{ $page }}</a>
        @endif
      @endforeach
    @endif
  @endforeach

  {{-- Next --}}
  @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}" class="pg-btn"><i class="ri-arrow-right-s-line"></i></a>
  @else
    <button class="pg-btn" disabled><i class="ri-arrow-right-s-line"></i></button>
  @endif
</nav>
@endif