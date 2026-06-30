@props([
    'id',
    'title',
    'expanded' => false,
    'panelClass' => 'default',
    'icon' => '',
    'headingClass' => '',
])

@php
    $headingId = $id . '-heading';
    $collapseId = $id . '-collapse';
@endphp

<div class="panel panel-{{ $panelClass }}">
    <div class="panel-heading {{ $headingClass }}" role="tab" id="{{ $headingId }}">
        <h4 class="panel-title">
            <a role="button"
               data-toggle="collapse"
               data-parent="#{{ $attributes->get('parent') }}"
               href="#{{ $collapseId }}"
               aria-expanded="{{ $expanded ? 'true' : 'false' }}"
               aria-controls="{{ $collapseId }}">
                @if($icon)
                    <i class="fa fa-{{ $icon }}"></i>
                @endif
                {{ $title }}
                <span class="accordion-chevron pull-right">
                    <i class="fa fa-chevron-{{ $expanded ? 'up' : 'down' }}"></i>
                </span>
            </a>
        </h4>
    </div>
    <div id="{{ $collapseId }}"
         class="panel-collapse collapse{{ $expanded ? ' in' : '' }}"
         role="tabpanel"
         aria-labelledby="{{ $headingId }}">
        <div class="panel-body">
            {{ $slot }}
        </div>
    </div>
</div>
