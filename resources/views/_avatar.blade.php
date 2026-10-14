@if(! empty($avatar))
    <img src="{{ $avatar }}" alt="" class="ptl-avatar" loading="lazy" width="{{ $size ?? 40 }}" height="{{ $size ?? 40 }}" @isset($size) style="width: {{ $size }}px; height: {{ $size }}px" @endisset>
@else
    <span class="ptl-avatar ptl-avatar-placeholder" aria-hidden="true" @isset($size) style="width: {{ $size }}px; height: {{ $size }}px" @endisset>{{ Str::upper(Str::substr($name, 0, 1)) }}</span>
@endif
