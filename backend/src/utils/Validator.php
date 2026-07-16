<?php
// =====================================================
// POS SYSTEM - VALIDATOR CLASS
// =====================================================

class Validator {
    private $errors = [];

    public function validate($data, $rules) {
        foreach ($rules as $field => $fieldRules) {
            $rules_array = explode('|', $fieldRules);
            
            foreach ($rules_array as $rule) {
                $this->applyRule($field, $data[$field] ?? null, $rule);
            }
        }

        return empty($this->errors);
    }

    private function applyRule($field, $value, $rule) {
        $rule_parts = explode(':', $rule);
        $rule_name = $rule_parts[0];
        $rule_param = $rule_parts[1] ?? null;

        switch ($rule_name) {
            case 'required':
                if (empty($value)) {
                    $this->errors[$field][] = ucfirst($field) . ' is required';
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = ucfirst($field) . ' must be a valid email';
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < $rule_param) {
                    $this->errors[$field][] = ucfirst($field) . ' must be at least ' . $rule_param . ' characters';
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > $rule_param) {
                    $this->errors[$field][] = ucfirst($field) . ' must not exceed ' . $rule_param . ' characters';
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field][] = ucfirst($field) . ' must be numeric';
                }
                break;

            case 'unique':
                // Will be handled separately with database
                break;
        }
    }

    public function getErrors() {
        return $this->errors;
    }
}
