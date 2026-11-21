<?php
declare(strict_types=1);

namespace App\Core;

class Validator
{
    // 8-100 chars, no spaces, at least one lowercase, uppercase, digit and symbol
    public const STRONG_PASSWORD = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S{8,100}$/';
    public const STRONG_PASSWORD_HTML = '(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S{8,100}';
    public const STRONG_PASSWORD_MESSAGE = 'Use at least 8 characters with an uppercase letter, a lowercase letter, a number and a symbol, and no spaces.';

    private array $input;
    private array $rules;
    private array $errors = [];
    private array $data = [];

    public function __construct(array $input, array $rules)
    {
        $this->input = $input;
        $this->rules = $rules;
        $this->run();
    }

    public function passes(): bool
    {
        return !$this->errors;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function data(): array
    {
        return $this->data;
    }

    private function label(string $field): string
    {
        return ucfirst(str_replace('_', ' ', $field));
    }

    private function run(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $raw = $this->input[$field] ?? '';
            $value = is_string($raw) ? trim($raw) : $raw;
            $rules = explode('|', $ruleString);
            $required = in_array('required', $rules, true);
            if ($value === '' || $value === null || $value === []) {
                if ($required) {
                    $this->errors[$field] = $this->label($field) . ' is required.';
                }
                $this->data[$field] = in_array('int', $rules, true) || in_array('numeric', $rules, true) ? null : '';
                continue;
            }
            foreach ($rules as $rule) {
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);
                $error = $this->check($name, $arg, $value, $field);
                if ($error) {
                    $this->errors[$field] = $error;
                    break;
                }
            }
            if (in_array('int', $rules, true)) {
                $value = (int) $value;
            } elseif (in_array('numeric', $rules, true)) {
                $value = (float) $value;
            }
            $this->data[$field] = $value;
        }
    }

    private function check(string $name, ?string $arg, $value, string $field): ?string
    {
        $label = $this->label($field);
        switch ($name) {
            case 'required':
                return null;
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) ? null : 'Enter a valid email address.';
            case 'min':
                return mb_strlen((string) $value) >= (int) $arg ? null : "$label must be at least $arg characters.";
            case 'max':
                return mb_strlen((string) $value) <= (int) $arg ? null : "$label must be at most $arg characters.";
            case 'int':
                return filter_var($value, FILTER_VALIDATE_INT) !== false ? null : "$label must be a whole number.";
            case 'numeric':
                return is_numeric($value) ? null : "$label must be a number.";
            case 'between':
                [$lo, $hi] = explode(',', (string) $arg);
                return is_numeric($value) && $value >= $lo && $value <= $hi ? null : "$label must be between $lo and $hi.";
            case 'in':
                return in_array((string) $value, explode(',', (string) $arg), true) ? null : "Choose a valid $label.";
            case 'date':
                $d = \DateTime::createFromFormat('Y-m-d', (string) $value);
                return $d && $d->format('Y-m-d') === $value ? null : "$label must be a valid date.";
            case 'confirmed':
                return ($this->input[$field . '_confirm'] ?? null) === $value ? null : "The $label confirmation doesn't match.";
            case 'phone':
                return preg_match('/^[0-9+()\s-]{7,20}$/', (string) $value) ? null : 'Enter a valid phone number.';
            case 'strong_password':
                return preg_match(self::STRONG_PASSWORD, (string) $value) ? null : self::STRONG_PASSWORD_MESSAGE;
            default:
                return null;
        }
    }
}
