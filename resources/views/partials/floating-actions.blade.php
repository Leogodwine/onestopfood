@php
    $currentLocale = app()->getLocale();
    $waPhone = preg_replace('/\D+/', '', $supportPhone ?? config('contacts.support_phone', '+255 651 490 677'));
    if (str_starts_with($waPhone, '0')) {
        $waPhone = '255' . substr($waPhone, 1);
    }
    $waMessage = __('common.whatsapp_message', ['brand' => $brand ?? config('app.name')]);
@endphp
<div class="floating-actions-stack" aria-label="{{ __('nav.language') }}">
    <div class="lang-fab-switcher" role="group">
        <a href="{{ route('locale.switch', 'en') }}"
           class="lang-fab-btn {{ $currentLocale === 'en' ? 'active' : '' }}"
           title="{{ __('nav.english') }}"
           aria-label="{{ __('nav.english') }}">EN</a>
        <a href="{{ route('locale.switch', 'sw') }}"
           class="lang-fab-btn {{ $currentLocale === 'sw' ? 'active' : '' }}"
           title="{{ __('nav.swahili') }}"
           aria-label="{{ __('nav.swahili') }}">SW</a>
    </div>
    <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode($waMessage) }}"
       target="_blank"
       rel="noopener"
       class="whatsapp-fab"
       title="{{ __('common.chat_whatsapp') }}"
       aria-label="{{ __('common.chat_whatsapp') }}">
        <i class="bi bi-whatsapp"></i>
    </a>
</div>
