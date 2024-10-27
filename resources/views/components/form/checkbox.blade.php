@vite('resources/views/components/form/checkbox.css')

<label class="form-group checkbox-group-item" for="checkbox-{{$idx}}">
    {{ $label }}
    <input
        id="checkbox-{{$idx}}"
        type="checkbox"
        @if($disabled) disabled @endif
        @if($checked) checked @endif
        name="{{$name}}"
        class="form-control"
        value="{{ $value }}"
    >
</label>
