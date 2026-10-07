<?php
declare(strict_types=1);

namespace App\Core;

class Validator
{
    protected array $data;
    protected array $rules;
    protected array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public static function make(array $data, array $rules): self
    {
        $validator = new self($data, $rules);
        $validator->validate();
        return $validator;
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $rulesList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $value = $this->data[$field] ?? null;

            foreach ($rulesList as $ruleStr) {
                [$rule, $param] = array_pad(explode(':', $ruleStr, 2), 2, null);

                if ($rule === 'required') {
                    if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                        $this->addError($field, "The {$field} field is required.");
                        break;
                    }
                }

                // If not required and value is empty, skip remaining validations for this field
                if (($value === null || $value === '') && $rule !== 'required') {
                    continue;
                }

                if ($rule === 'email') {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->addError($field, "The {$field} must be a valid email address.");
                    }
                } elseif ($rule === 'phone') {
                    $cleaned = preg_replace('/\D/', '', (string)$value);
                    if (!preg_match('/^[6-9]\d{9}$/', $cleaned)) {
                        $this->addError($field, "Please enter a valid 10-digit mobile number.");
                    }
                } elseif ($rule === 'min') {
                    $min = (int)$param;
                    if (is_numeric($value)) {
                        if ((float)$value < $min) {
                            $this->addError($field, "The {$field} must be at least {$min}.");
                        }
                    } elseif (mb_strlen((string)$value) < $min) {
                        $this->addError($field, "The {$field} must be at least {$min} characters.");
                    }
                } elseif ($rule === 'max') {
                    $max = (int)$param;
                    if (is_numeric($value)) {
                        if ((float)$value > $max) {
                            $this->addError($field, "The {$field} may not be greater than {$max}.");
                        }
                    } elseif (mb_strlen((string)$value) > $max) {
                        $this->addError($field, "The {$field} may not be greater than {$max} characters.");
                    }
                } elseif ($rule === 'numeric') {
                    if (!is_numeric($value)) {
                        $this->addError($field, "The {$field} must be a number.");
                    }
                } elseif ($rule === 'in') {
                    $allowed = explode(',', (string)$param);
                    if (!in_array((string)$value, $allowed, true)) {
                        $this->addError($field, "The selected {$field} is invalid.");
                    }
                } elseif ($rule === 'confirmed') {
                    $confirmField = $field . '_confirmation';
                    if (($this->data[$confirmField] ?? null) !== $value) {
                        $this->addError($field, "The {$field} confirmation does not match.");
                    }
                }
            }
        }

        return empty($this->errors);
    }

    protected function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(?string $field = null): ?string
    {
        if ($field !== null) {
            return $this->errors[$field][0] ?? null;
        }

        foreach ($this->errors as $messages) {
            if (!empty($messages[0])) {
                return $messages[0];
            }
        }

        return null;
    }
}
