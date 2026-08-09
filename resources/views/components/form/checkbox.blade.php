@vite('resources/views/components/form/checkbox.css')

<label class="form-group checkbox-group-item" for="checkbox-{{$idx}}" @if(isset($title)) title="{{ $title }}" @endif>
    {{ $label }}
    <input
        id="checkbox-{{$idx}}"
        type="checkbox"
        @if($disabled) disabled @endif
        @if($checked) checked @endif
        @if(isset($name) && $name !== '') name="{{$name}}" @endif
        class="form-control"
        value="{{ $value }}"
    >
</label>
