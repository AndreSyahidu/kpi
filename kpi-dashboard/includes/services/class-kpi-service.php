<?php
/**
 * KPI Service
 */
class KPI_Dashboard_KPI_Service {

    public static function create($data, $created_by = null) {
        $validation = KPI_Dashboard_Validator::validate_kpi($data);
        if ($validation !== true) {
            return new WP_Error('validation_error', __('Validation failed', 'kpi-dashboard'), $validation);
        }

        $data = KPI_Dashboard_Sanitizer::kpi_data($data);
        $data['created_by'] = $created_by;

        $kpi_id = KPI_Dashboard_KPI_Model::create($data);

        if (!$kpi_id) {
            return new WP_Error('create_failed', __('Failed to create KPI', 'kpi-dashboard'));
        }

        KPI_Dashboard_Audit_Log::log('create', 'kpi', $kpi_id, null, $data);
        KPI_Dashboard_Cache::clear_kpis();

        return KPI_Dashboard_KPI_Model::get($kpi_id);
    }

    public static function update($kpi_id, $data) {
        $kpi = KPI_Dashboard_KPI_Model::get($kpi_id);

        if (!$kpi) {
            return new WP_Error('not_found', __('KPI not found', 'kpi-dashboard'));
        }

        $validation = KPI_Dashboard_Validator::validate_kpi($data);
        if ($validation !== true) {
            return new WP_Error('validation_error', __('Validation failed', 'kpi-dashboard'), $validation);
        }

        $data = KPI_Dashboard_Sanitizer::kpi_data($data);
        $before = clone $kpi;

        $result = KPI_Dashboard_KPI_Model::update($kpi_id, $data);

        if ($result === false) {
            return new WP_Error('update_failed', __('Failed to update KPI', 'kpi-dashboard'));
        }

        KPI_Dashboard_Audit_Log::log('update', 'kpi', $kpi_id, $before, $data);
        KPI_Dashboard_Cache::clear_kpis();

        return KPI_Dashboard_KPI_Model::get($kpi_id);
    }

    public static function delete($kpi_id) {
        $kpi = KPI_Dashboard_KPI_Model::get($kpi_id);

        if (!$kpi) {
            return new WP_Error('not_found', __('KPI not found', 'kpi-dashboard'));
        }

        // Check if KPI has data entries
        $data_count = count(KPI_Dashboard_KPI_Data_Model::get_all(['kpi_id' => $kpi_id, 'limit' => 1]));
        if ($data_count > 0) {
            return new WP_Error('has_data', __('Cannot delete KPI with existing data entries. Please archive instead.', 'kpi-dashboard'));
        }

        $result = KPI_Dashboard_KPI_Model::delete($kpi_id);

        if (!$result) {
            return new WP_Error('delete_failed', __('Failed to delete KPI', 'kpi-dashboard'));
        }

        KPI_Dashboard_Audit_Log::log('delete', 'kpi', $kpi_id, $kpi, null);
        KPI_Dashboard_Cache::clear_kpis();

        return true;
    }

    public static function assign_to_department($kpi_id, $department_id, $target_override = null, $assigned_by = null) {
        $kpi = KPI_Dashboard_KPI_Model::get($kpi_id);
        if (!$kpi) {
            return new WP_Error('kpi_not_found', __('KPI not found', 'kpi-dashboard'));
        }

        $dept = KPI_Dashboard_Department_Model::get($department_id);
        if (!$dept) {
            return new WP_Error('dept_not_found', __('Department not found', 'kpi-dashboard'));
        }

        $result = KPI_Dashboard_KPI_Model::assign($kpi_id, 'department', $department_id, $target_override, $assigned_by);

        if (!$result) {
            return new WP_Error('assign_failed', __('Failed to assign KPI', 'kpi-dashboard'));
        }

        KPI_Dashboard_Audit_Log::log('assign_kpi', 'kpi', $kpi_id, null, [
            'type' => 'department',
            'id' => $department_id,
            'target_override' => $target_override
        ]);

        return true;
    }

    public static function assign_to_position($kpi_id, $position_id, $target_override = null, $assigned_by = null) {
        $kpi = KPI_Dashboard_KPI_Model::get($kpi_id);
        if (!$kpi) {
            return new WP_Error('kpi_not_found', __('KPI not found', 'kpi-dashboard'));
        }

        $position = KPI_Dashboard_Position_Model::get($position_id);
        if (!$position) {
            return new WP_Error('position_not_found', __('Position not found', 'kpi-dashboard'));
        }

        $result = KPI_Dashboard_KPI_Model::assign($kpi_id, 'position', $position_id, $target_override, $assigned_by);

        if (!$result) {
            return new WP_Error('assign_failed', __('Failed to assign KPI', 'kpi-dashboard'));
        }

        KPI_Dashboard_Audit_Log::log('assign_kpi', 'kpi', $kpi_id, null, [
            'type' => 'position',
            'id' => $position_id,
            'target_override' => $target_override
        ]);

        return true;
    }

    public static function get_with_assignments($kpi_id) {
        $kpi = KPI_Dashboard_KPI_Model::get($kpi_id);

        if (!$kpi) {
            return null;
        }

        $kpi->assignments = KPI_Dashboard_KPI_Model::get_assignments($kpi_id);

        // Decode JSON fields
        if ($kpi->formula) {
            $kpi->formula = json_decode($kpi->formula, true);
        }
        if ($kpi->color_scheme) {
            $kpi->color_scheme = json_decode($kpi->color_scheme, true);
        }
        if ($kpi->validation_rules) {
            $kpi->validation_rules = json_decode($kpi->validation_rules, true);
        }

        return $kpi;
    }

    public static function get_kpis_for_user($user) {
        $kpis = [];

        // Get department KPIs
        if ($user->department_id) {
            $dept_kpis = KPI_Dashboard_KPI_Model::get_kpis_for_department($user->department_id);
            $kpis = array_merge($kpis, $dept_kpis);
        }

        // Get position KPIs
        if ($user->position_id) {
            // TODO: implement get_kpis_for_position
        }

        // Remove duplicates
        $seen = [];
        $unique_kpis = [];
        foreach ($kpis as $kpi) {
            if (!in_array($kpi->id, $seen)) {
                $seen[] = $kpi->id;
                $unique_kpis[] = $kpi;
            }
        }

        return $unique_kpis;
    }
}
