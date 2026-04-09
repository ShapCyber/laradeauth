@php
    $name = $name ?? 'verification_code';
    $id = $id ?? $name;
    $autoSubmit = ($autoSubmit ?? false) ? 'true' : 'false';
    $normalizedValue = preg_replace('/\D+/', '', (string) ($value ?? '')) ?? '';
    $normalizedValue = substr($normalizedValue, 0, 6);
    $digits = str_split(str_pad($normalizedValue, 6, ' '));
@endphp

<div
    class="otp-input-root"
    data-otp-input-root
    data-otp-auto-submit="{{ $autoSubmit }}"
    @isset($methodField) data-otp-method-field="{{ $methodField }}" @endisset
    @isset($methodValue) data-otp-method-value="{{ $methodValue }}" @endisset
>
    <input
        type="hidden"
        name="{{ $name }}"
        value="{{ $normalizedValue }}"
        data-otp-hidden-input
        @if (! empty($required)) required @endif
    >

    <div class="otp-input-grid">
        @foreach ($digits as $index => $digit)
            <input
                id="{{ $index === 0 ? $id : $id.'_'.$index }}"
                class="form-control form-control-lg otp-digit-input"
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="1"
                autocomplete="{{ $index === 0 ? 'one-time-code' : 'off' }}"
                value="{{ trim($digit) }}"
                data-otp-digit
                aria-label="Verification code digit {{ $index + 1 }}"
            >
        @endforeach
    </div>
</div>
