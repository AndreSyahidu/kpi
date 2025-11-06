export interface User {
  id: number;
  username: string;
  email: string;
  full_name: string;
  avatar_url?: string;
  role: 'super_admin' | 'dept_head' | 'manager' | 'staff';
  department_id?: number;
  position_id?: number;
  is_active: boolean;
  last_login?: string;
  permissions?: any;
  department?: Department;
  position?: Position;
}

export interface Department {
  id: number;
  name: string;
  slug: string;
  description?: string;
  parent_id?: number;
  color_code: string;
  icon_class?: string;
  sort_order: number;
  is_active: boolean;
}

export interface Position {
  id: number;
  title: string;
  department_id: number;
  level: 'junior' | 'senior' | 'lead' | 'manager' | 'director';
  description?: string;
  is_active: boolean;
}

export interface KPI {
  id: number;
  name: string;
  slug: string;
  description?: string;
  category: string;
  type: 'department' | 'position' | 'personal';
  metric_type: 'number' | 'percentage' | 'ratio' | 'custom' | 'boolean';
  unit?: string;
  input_frequency: 'daily' | 'weekly' | 'monthly' | 'quarterly' | 'yearly';
  target_value?: number;
  target_type: 'minimum' | 'maximum' | 'exact' | 'range';
  chart_type: string;
  is_active: boolean;
}

export interface KPIData {
  id: number;
  kpi_id: number;
  department_id: number;
  position_id?: number;
  user_id?: number;
  period_start: string;
  period_end: string;
  value: number;
  unit?: string;
  status: 'draft' | 'pending' | 'approved' | 'rejected';
  notes?: string;
  submitted_by: number;
  submitted_at: string;
  reviewed_by?: number;
  reviewed_at?: string;
  review_notes?: string;
}

export interface Notification {
  id: number;
  type: 'reminder' | 'approval' | 'alert' | 'info' | 'success' | 'warning' | 'error';
  severity: 'info' | 'warning' | 'critical' | 'success';
  title: string;
  message: string;
  action_url?: string;
  is_read: boolean;
  created_at: string;
}

export interface AuthResponse {
  success: boolean;
  user: User;
  access_token: string;
  refresh_token: string;
  expires_in: number;
}

export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  code?: string;
}
