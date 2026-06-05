@php
    $headerClass = trim('page-header page-header-split ' . ($class ?? '') . (!empty($mb) ? ' ' . $mb : ''));
@endphp
<div class="{{ $headerClass }}">
    <div @class([
        'd-flex justify-content-between align-items-center page-header-top',
        'page-header-top--solo' => empty($actions),
    ])>
        <h2 class="mb-0">{!! $title !!}</h2>
        @if(!empty($actions))
            <div class="page-header-actions">
                {!! $actions !!}
            </div>
        @endif
    </div>
    @if(!empty($subtitle))
        <div class="text-muted page-header-subtitle mb-0">{!! $subtitle !!}</div>
    @endif
    @if(!empty($actionsSecondary))
        <div class="page-header-actions page-header-actions-secondary">
            {!! $actionsSecondary !!}
        </div>
    @endif
    @if(!empty($extra))
        <div class="page-header-extra">{!! $extra !!}</div>
    @endif
</div>
