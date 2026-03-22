<?php

class DataValidator {
    private $errors = [];
    private $rules = [];
    
    public function validate($data, $rules) {
        $this->errors = [];
        $this->rules = $rules;
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (is_string($rule)) {
                $this->validateField($field, $value, $rule);
            } elseif (is_array($rule)) {
                foreach ($rule as $singleRule) {
                    $this->validateField($field, $value, $singleRule);
                }
            }
        }
        
        return empty($this->errors);
    }
    
    private function validateField($field, $value, $rule) {
        $rules = explode('|', $rule);
        
        foreach ($rules as $singleRule) {
            if ($singleRule === 'required') {
                if (empty($value) && $value !== '0') {
                    $this->errors[$field][] = "$field is required";
                }
            } elseif ($singleRule === 'email') {
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "$field must be a valid email";
                }
            } elseif ($singleRule === 'numeric') {
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field][] = "$field must be numeric";
                }
            } elseif ($singleRule === 'integer') {
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->errors[$field][] = "$field must be an integer";
                }
            } elseif ($singleRule === 'string') {
                if (!empty($value) && !is_string($value)) {
                    $this->errors[$field][] = "$field must be a string";
                }
            } elseif (strpos($singleRule, 'min:') === 0) {
                $min = (int) substr($singleRule, 4);
                if (!empty($value) && strlen($value) < $min) {
                    $this->errors[$field][] = "$field must be at least $min characters";
                }
            } elseif (strpos($singleRule, 'max:') === 0) {
                $max = (int) substr($singleRule, 4);
                if (!empty($value) && strlen($value) > $max) {
                    $this->errors[$field][] = "$field must not exceed $max characters";
                }
            } elseif (strpos($singleRule, 'between:') === 0) {
                $range = explode(',', substr($singleRule, 8));
                $min = (float) $range[0];
                $max = (float) $range[1];
                if (!empty($value) && (is_numeric($value) && ($value < $min || $value > $max))) {
                    $this->errors[$field][] = "$field must be between $min and $max";
                }
            } elseif (strpos($singleRule, 'in:') === 0) {
                $allowed = explode(',', substr($singleRule, 3));
                if (!empty($value) && !in_array($value, $allowed)) {
                    $this->errors[$field][] = "$field must be one of: " . implode(', ', $allowed);
                }
            } elseif ($singleRule === 'url') {
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[$field][] = "$field must be a valid URL";
                }
            } elseif ($singleRule === 'boolean') {
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) {
                    $this->errors[$field][] = "$field must be true or false";
                }
            } elseif ($singleRule === 'date') {
                if (!empty($value)) {
                    $date = DateTime::createFromFormat('Y-m-d', $value);
                    if (!$date || $date->format('Y-m-d') !== $value) {
                        $this->errors[$field][] = "$field must be a valid date (Y-m-d)";
                    }
                }
            } elseif ($singleRule === 'datetime') {
                if (!empty($value)) {
                    $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);
                    if (!$date || $date->format('Y-m-d H:i:s') !== $value) {
                        $this->errors[$field][] = "$field must be a valid datetime (Y-m-d H:i:s)";
                    }
                }
            } elseif (strpos($singleRule, 'regex:') === 0) {
                $pattern = substr($singleRule, 6);
                if (!empty($value) && !preg_match($pattern, $value)) {
                    $this->errors[$field][] = "$field format is invalid";
                }
            }
        }
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function getFirstError($field = null) {
        if ($field) {
            return $this->errors[$field][0] ?? null;
        }
        
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        
        return null;
    }
    
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    public function getErrorString($glue = ', ') {
        $allErrors = [];
        foreach ($this->errors as $fieldErrors) {
            $allErrors = array_merge($allErrors, $fieldErrors);
        }
        return implode($glue, $allErrors);
    }
    
    // Specialized validation methods
    public function validateCoordinates($latitude, $longitude) {
        $errors = [];
        
        if (!is_numeric($latitude) || $latitude < -90 || $latitude > 90) {
            $errors[] = "Latitude must be between -90 and 90";
        }
        
        if (!is_numeric($longitude) || $longitude < -180 || $longitude > 180) {
            $errors[] = "Longitude must be between -180 and 180";
        }
        
        return $errors;
    }
    
    public function validatePhoneNumber($phone) {
        if (!empty($phone)) {
            // Remove all non-numeric characters
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            
            if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 15) {
                return "Phone number must be between 10 and 15 digits";
            }
            
            if (!preg_match('/^[0-9]+$/', $cleanPhone)) {
                return "Phone number can only contain digits";
            }
        }
        
        return null;
    }
    
    public function validateIndonesianId($id) {
        if (!empty($id)) {
            if (strlen($id) !== 16) {
                return "Indonesian ID must be exactly 16 digits";
            }
            
            if (!preg_match('/^[0-9]+$/', $id)) {
                return "Indonesian ID can only contain digits";
            }
        }
        
        return null;
    }
    
    public function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}
