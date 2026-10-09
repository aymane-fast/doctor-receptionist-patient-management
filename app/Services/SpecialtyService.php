<?php

namespace App\Services;

use App\Models\Setting;
use InvalidArgumentException;

class SpecialtyService
{
    public const DEFAULT_SPECIALTY = 'general_practice';

    public function all(): array
    {
        return config('specialties', []);
    }

    public function keys(): array
    {
        return array_keys($this->all());
    }

    public function setupProfiles(): array
    {
        return array_intersect_key($this->all(), array_flip(['general_practice', 'pediatrics', 'dentistry', 'cardiology']));
    }

    public function supports(string $capability, ?string $specialty = null): bool
    {
        return (bool) data_get($this->profile($specialty), 'capabilities.'.$capability, false);
    }

    public function exists(?string $specialty): bool
    {
        return is_string($specialty) && array_key_exists($specialty, $this->all());
    }

    public function currentKey(): string
    {
        $specialty = (string) Setting::get('clinic_specialty', self::DEFAULT_SPECIALTY);

        return $this->exists($specialty) ? $specialty : self::DEFAULT_SPECIALTY;
    }

    public function current(): array
    {
        return $this->profile($this->currentKey());
    }

    public function profile(?string $specialty = null): array
    {
        $key = $specialty ?? $this->currentKey();
        $profile = $this->all()[$key] ?? $this->all()[self::DEFAULT_SPECIALTY];

        return array_merge(['key' => $key], $profile);
    }

    public function setCurrent(string $specialty): void
    {
        if (! $this->exists($specialty)) {
            throw new InvalidArgumentException("Unknown clinic specialty [{$specialty}].");
        }

        Setting::set('clinic_specialty', $specialty, 'string');
    }

    public function fields(string $section, ?string $specialty = null): array
    {
        return $this->profile($specialty)["{$section}_fields"] ?? [];
    }

    /**
     * Historical values remain readable after a clinic changes specialty.
     * Forms use fields(), while profile pages use this full label catalogue.
     */
    public function allFields(string $section): array
    {
        return collect($this->all())
            ->flatMap(fn (array $profile) => $profile["{$section}_fields"] ?? [])
            ->unique('key')
            ->values()
            ->all();
    }

    public function validationRules(string $section, ?string $specialty = null): array
    {
        $rules = ['specialty_data' => ['nullable', 'array']];

        foreach ($this->fields($section, $specialty) as $field) {
            $rules['specialty_data.'.$field['key']] = $field['rules'] ?? ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * Only profile-defined values are stored. Empty current-profile values remove
     * their previous values while data from an earlier specialty remains intact.
     */
    public function mergeSubmittedData(array $existing, ?array $submitted, string $section, ?string $specialty = null): array
    {
        $submitted ??= [];

        foreach ($this->fields($section, $specialty) as $field) {
            $key = $field['key'];

            if (! array_key_exists($key, $submitted)) {
                continue;
            }

            $value = $submitted[$key];
            if ($value === null || (is_string($value) && trim($value) === '')) {
                unset($existing[$key]);
                continue;
            }

            $existing[$key] = is_string($value) ? trim($value) : $value;
        }

        return $existing;
    }

    public function appointmentReasons(?string $specialty = null): array
    {
        return $this->profile($specialty)['appointment_reasons'] ?? [];
    }

    /**
     * Keep automatic bookings meaningful for the selected clinic without
     * overriding a reason explicitly entered by a staff member.
     */
    public function defaultAppointmentReason(?string $specialty = null): string
    {
        return $this->appointmentReasons($specialty)[0] ?? __('appointments.walk_in_consultation');
    }

    public function defaultFollowUpReason(?string $specialty = null): string
    {
        $reasons = $this->appointmentReasons($specialty);

        foreach ($reasons as $reason) {
            if (str_contains(strtolower($reason), 'follow')) {
                return $reason;
            }
        }

        return $this->defaultAppointmentReason($specialty);
    }

    /**
     * Medication examples are suggestions only; prescriptions stay free text
     * so clinicians are never limited to a pre-defined list.
     */
    public function medicationSuggestions(?string $specialty = null): array
    {
        return $this->seed('medications', $specialty, []);
    }

    /**
     * Preserve human-readable historical specialty values in exports and
     * clinical summaries after a clinic changes its active profile.
     */
    public function formatData(string $section, ?array $values): string
    {
        if (empty($values)) {
            return '';
        }

        $formatted = [];
        foreach ($this->allFields($section) as $field) {
            $value = $values[$field['key']] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $formatted[] = $field['label'].': '.(is_array($value) ? implode(', ', $value) : $value);
        }

        return implode('; ', $formatted);
    }

    public function seed(string $key, ?string $specialty = null, mixed $default = null): mixed
    {
        return data_get($this->profile($specialty), 'seed.'.$key, $default);
    }

    public function suggestedDefaults(?string $specialty = null): array
    {
        return $this->profile($specialty)['defaults'] ?? [];
    }
}
