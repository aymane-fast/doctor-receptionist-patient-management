<details id="scheduling_override_fields" class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3" @if(old('scheduling_override', $appointment?->scheduling_override)) open @endif>
    <summary class="cursor-pointer text-sm font-semibold text-amber-900">{{ __('workflow.scheduling_exception') }}</summary>
    <label class="mt-2 flex items-start gap-2 text-sm">
        <input type="checkbox" name="scheduling_override" value="1" @checked(old('scheduling_override', $appointment?->scheduling_override))>
        <span>{{ __('workflow.override_confirm') }}</span>
    </label>
    <label class="mt-2 block text-sm" for="override_reason">{{ __('workflow.override_reason') }}</label>
    <input id="override_reason" name="override_reason" maxlength="255" value="{{ old('override_reason', $appointment?->override_reason) }}">
</details>
