@forelse ($distributors as $item)
    <p>
        @can('view distributor')
            <a href="{{ url('distributors/'.$item->id) }}" class="text-decoration-none">
                {{ $item->name ?? '' }} ({{ $item->states->name ?? '' }})
            </a>
        @else
            {{ $item->name ?? '' }} ({{ $item->states->name ?? '' }})
        @endcan
    </p>
@empty
    <p class="text-muted">No Distributor found</p>
@endforelse
