<?php
/**
 * Sanitization Utility
 */
class KPI_Dashboard_Sanitizer {

    /**
     * Sanitize text field
     */
    public static function text($value) {
        return sanitize_text_field($value);
    }

    /**
     * Sanitize email
     */
    public static function email($value) {
        return sanitize_email($value);
    }

    /**
     * Sanitize textarea
     */
    public static function textarea($value) {
        return sanitize_textarea_field($value);
    }

    /**
     * Sanitize HTML
     */
    public static function html($value) {
        return wp_kses_post($value);
    }

    /**
     * Sanitize number
     */
    public static function number($value) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitize integer
     */
    public static function int($value) {
        return (int) $value;
    }

    /**
     * Sanitize boolean
     */
    public static function bool($value) {
        return (bool) $value;
    }

    /**
     * Sanitize URL
     */
    public static function url($value) {
        return esc_url_raw($value);
    }

    /**
     * Sanitize slug
     */
    public static function slug($value) {
        return sanitize_title($value);
    }

    /**
     * Sanitize user data
     */
    public static function user_data($data) {
        $sanitized = [];

        $text_fields = ['username', 'full_name', 'role'];
        foreach ($text_fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = self::text($data[$field]);
            }
        }

        if (isset($data['email'])) {
            $sanitized['email'] = self::email($data['email']);
        }

        if (isset($data['password'])) {
            $sanitized['password'] = $data['password']; // Don't sanitize passwords
        }

        if (isset($data['avatar_url'])) {
            $sanitized['avatar_url'] = self::url($data['avatar_url']);
        }

        $int_fields = ['department_id', 'position_id', 'is_active', 'wp_user_id', 'created_by'];
        foreach ($int_fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = self::int($data[$field]);
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize department data
     */
    public static function department_data($data) {
        $sanitized = [];

        if (isset($data['name'])) {
            $sanitized['name'] = self::text($data['name']);
        }

        if (isset($data['slug'])) {
            $sanitized['slug'] = self::slug($data['slug']);
        }

        if (isset($data['description'])) {
            $sanitized['description'] = self::textarea($data['description']);
        }

        if (isset($data['color_code'])) {
            $sanitized['color_code'] = self::text($data['color_code']);
        }

        if (isset($data['icon_class'])) {
            $sanitized['icon_class'] = self::text($data['icon_class']);
        }

        if (isset($data['logo_url'])) {
            $sanitized['logo_url'] = self::url($data['logo_url']);
        }

        $int_fields = ['parent_id', 'sort_order', 'is_active', 'created_by'];
        foreach ($int_fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = self::int($data[$field]);
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize KPI definition data
     */
    public static function kpi_data($data) {
        $sanitized = [];

        $text_fields = ['name', 'slug', 'category', 'type', 'metric_type', 'unit',
                       'input_frequency', 'calculation_method', 'target_type', 'chart_type'];
        foreach ($text_fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = self::text($data[$field]);
            }
        }

        if (isset($data['description'])) {
            $sanitized['description'] = self::textarea($data['description']);
        }

        $number_fields = ['target_value', 'target_range_min', 'target_range_max'];
        foreach ($number_fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = self::number($data[$field]);
            }
        }

        $int_fields = ['weight', 'decimal_places', 'is_active', 'created_by'];
        foreach ($int_fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = self::int($data[$field]);
            }
        }

        // JSON fields - validate and sanitize
        $json_fields = ['formula', 'color_scheme', 'validation_rules'];
        foreach ($json_fields as $field) {
            if (isset($data[$field])) {
                if (is_string($data[$field])) {
                    $decoded = json_decode($data[$field], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $sanitized[$field] = $decoded;
                    }
                } elseif (is_array($data[$field])) {
                    $sanitized[$field] = $data[$field];
                }
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize KPI data entry
     */
    public static function kpi_entry_data($data) {
        $sanitized = [];

        $int_fields = ['kpi_id', 'department_id', 'position_id', 'user_id', 'submitted_by', 'reviewed_by'];
        foreach ($int_fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = self::int($data[$field]);
            }
        }

        if (isset($data['value'])) {
            $sanitized['value'] = self::number($data['value']);
        }

        if (isset($data['unit'])) {
            $sanitized['unit'] = self::text($data['unit']);
        }

        if (isset($data['status'])) {
            $sanitized['status'] = self::text($data['status']);
        }

        if (isset($data['notes'])) {
            $sanitized['notes'] = self::textarea($data['notes']);
        }

        if (isset($data['review_notes'])) {
            $sanitized['review_notes'] = self::textarea($data['review_notes']);
        }

        if (isset($data['period_start'])) {
            $sanitized['period_start'] = self::text($data['period_start']);
        }

        if (isset($data['period_end'])) {
            $sanitized['period_end'] = self::text($data['period_end']);
        }

        if (isset($data['attachments'])) {
            if (is_string($data['attachments'])) {
                $decoded = json_decode($data['attachments'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $sanitized['attachments'] = $decoded;
                }
            } elseif (is_array($data['attachments'])) {
                $sanitized['attachments'] = $data['attachments'];
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize array recursively
     */
    public static function array_deep($array) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::array_deep($value);
            } else {
                $array[$key] = self::text($value);
            }
        }
        return $array;
    }
}
