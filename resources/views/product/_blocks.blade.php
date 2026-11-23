{{-- Renders description blocks: ['h', text] | ['p', text] | ['ul', [..]] | ['ol', [..]] --}}
@foreach ($blocks ?? [] as [$type, $content])
    @switch($type)
        @case('h')<h3>{{ $content }}</h3>@break
        @case('p')<p>{{ $content }}</p>@break
        @case('ul')<ul class="ticks">@foreach ($content as $item)<li>{{ $item }}</li>@endforeach</ul>@break
        @case('ol')<ol class="steps">@foreach ($content as $item)<li>{{ $item }}</li>@endforeach</ol>@break
    @endswitch
@endforeach
