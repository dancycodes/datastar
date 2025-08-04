 @props([
    'label' => null,
    'name' => '',
    'placeholder' => '',
    'type' => 'text',
    'has_validation' => true,
    'field_validates_on' => 'change',
    'field_validates_controller' => null,
    'field_validates_key' => null,
 ])


@if(!in_array($type,['checkbox', 'radio']))
    <div class="w-full">
        @if($label)
            <label for="{{ $name }}" class="text-sm font-medium">{{ $label }}</label>
        @endif
        <input
            id="{{ $name }}"
            data-bind="{{ $name }}"
            type="{{ $type }}"
            class="text-sm w-full outline-none focus:border-2 focus:border-blue-500 p-1.5 border rounded"
            placeholder="{{ $placeholder }}"
            @if($field_validates_controller)
                data-on-{{ $field_validates_on }}="{{ datastar()->action([$field_validates_controller, 'fieldValidate'], ['field' => $name, 'key' => $field_validates_key]) }}"
            @endif
            >
        <div class="text-red-500 text-sm mt-1" data-show="$errors?.{{ $name }}" data-text="$errors?.{{ $name }}"></div>
    </div>
@endif
