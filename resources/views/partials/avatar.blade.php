@php($size = $size ?? 32)
@if ($user->photoUrl)
    <img src="{{ $user->photoUrl }}" alt="" width="{{ $size }}" height="{{ $size }}" class="rounded-circle object-fit-cover" referrerpolicy="no-referrer">
@else
    <span class="avatar rounded-circle d-inline-flex align-items-center justify-content-center fw-semibold"
          style="width: {{ $size }}px; height: {{ $size }}px; font-size: {{ max(10, (int) ($size / 2.6)) }}px">{{ $user->initials() }}</span>
@endif
