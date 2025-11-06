<?php
/**
 * Validation Utility
 */
class KPI_Dashboard_Validator {

    /**
     * Validate user data
     */
    public static function validate_user($data, $is_update = false) {
        $errors = [];

        // Username
        if (!$is_update || isset($data['username'])) {
            if (empty($data['username'])) {
                $errors['username'] = __('Username is required', 'kpi-dashboard');
            } elseif (strlen($data['username']) < 3) {
                $errors['username'] = __('Username must be at least 3 characters', 'kpi-dashboard');
            } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
                $errors['username'] = __('Username can only contain letters, numbers, and underscores', 'kpi-dashboard');
            }
        }

        // Email
        if (!$is_update || isset($data['email'])) {
            if (empty($data['email'])) {
                $errors['email'] = __('Email is required', 'kpi-dashboard');
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = __('Invalid email format', 'kpi-dashboard');
            }
        }

        // Password (only for create or if password is being changed)
        if (!$is_update && empty($data['password'])) {
            $errors['password'] = __('Password is required', 'kpi-dashboard');
        } elseif (isset($data['password']) && !empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                $errors['password'] = __('Password must be at least 6 characters', 'kpi-dashboard');
            }
        }

        // Full name
        if (!$is_update || isset($data['full_name'])) {
            if (empty($data['full_name'])) {
                $errors['full_name'] = __('Full name is required', 'kpi-dashboard');
            }
        }

        // Role
        if (isset($data['role'])) {
            $valid_roles = ['super_admin', 'dept_head', 'manager', 'staff'];
            if (!in_array($data['role'], $valid_roles)) {
                $errors['role'] = __('Invalid role', 'kpi-dashboard');
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Validate department data
     */
    public static function validate_department($data) {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = __('Department name is required', 'kpi-dashboard');
        }

        if (isset($data['color_code']) && !preg_match('/^#[0-9A-F]{6}$/i', $data['color_code'])) {
            $errors['color_code'] = __('Invalid color code format (use #RRGGBB)', 'kpi-dashboard');
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Validate KPI definition data
     */
    public static function validate_kpi($data) {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = __('KPI name is required', 'kpi-dashboard');
        }

        $valid_types = ['department', 'position', 'personal'];
        if (isset($data['type']) && !in_array($data['type'], $valid_types)) {
            $errors['type'] = __('Invalid KPI type', 'kpi-dashboard');
        }

        $valid_metric_types = ['number', 'percentage', 'ratio', 'custom', 'boolean'];
        if (isset($data['metric_type']) && !in_array($data['metric_type'], $valid_metric_types)) {
            $errors['metric_type'] = __('Invalid metric type', 'kpi-dashboard');
        }

        $valid_frequencies = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];
        if (isset($data['input_frequency']) && !in_array($data['input_frequency'], $valid_frequencies)) {
            $errors['input_frequency'] = __('Invalid input frequency', 'kpi-dashboard');
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Validate KPI data entry
     */
    public static function validate_kpi_data($data, $kpi_definition = null) {
        $errors = [];

        if (empty($data['kpi_id'])) {
            $errors['kpi_id'] = __('KPI ID is required', 'kpi-dashboard');
        }

        if (empty($data['department_id'])) {
            $errors['department_id'] = __('Department ID is required', 'kpi-dashboard');
        }

        if (empty($data['period_start'])) {
            $errors['period_start'] = __('Period start date is required', 'kpi-dashboard');
        }

        if (empty($data['period_end'])) {
            $errors['period_end'] = __('Period end date is required', 'kpi-dashboard');
        }

        if (isset($data['period_start']) && isset($data['period_end'])) {
            if (strtotime($data['period_start']) > strtotime($data['period_end'])) {
                $errors['period'] = __('Period start date must be before end date', 'kpi-dashboard');
            }
        }

        if (!isset($data['value']) || $data['value'] === '') {
            $errors['value'] = __('Value is required', 'kpi-dashboard');
        } elseif (!is_numeric($data['value'])) {
            $errors['value'] = __('Value must be a number', 'kpi-dashboard');
        }

        // Validate against KPI definition rules
        if ($kpi_definition && isset($kpi_definition->validation_rules)) {
            $rules = json_decode($kpi_definition->validation_rules, true);
            if ($rules) {
                if (isset($rules['min']) && $data['value'] < $rules['min']) {
                    $errors['value'] = sprintf(__('Value must be at least %s', 'kpi-dashboard'), $rules['min']);
                }
                if (isset($rules['max']) && $data['value'] > $rules['max']) {
                    $errors['value'] = sprintf(__('Value must not exceed %s', 'kpi-dashboard'), $rules['max']);
                }
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Validate date format
     */
    public static function validate_date($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Validate date range
     */
    public static function validate_date_range($start, $end) {
        if (!self::validate_date($start) || !self::validate_date($end)) {
            return false;
        }
        return strtotime($start) <= strtotime($end);
    }

    /**
     * Sanitize and validate file upload
     */
    public static function validate_file_upload($file) {
        $errors = [];

        if (!isset($file['error']) || is_array($file['error'])) {
            $errors[] = __('Invalid file upload', 'kpi-dashboard');
            return $errors;
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = __('File size exceeds limit', 'kpi-dashboard');
                break;
            default:
                $errors[] = __('File upload error', 'kpi-dashboard');
                break;
        }

        // Max 10MB
        if ($file['size'] > 10 * 1024 * 1024) {
            $errors[] = __('File size must not exceed 10MB', 'kpi-dashboard');
        }

        // Allowed file types
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.ms-excel',
                         'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                         'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                         'image/jpeg', 'image/png', 'image/gif'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types)) {
            $errors[] = __('Invalid file type', 'kpi-dashboard');
        }

        return empty($errors) ? true : $errors;
    }
}
