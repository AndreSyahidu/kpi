import apiService from './api.service';

export interface SystemSettings {
  company_name: string;
  company_logo: string;
  primary_color: string;
  secondary_color: string;
  date_format: string;
  time_format: string;
  timezone: string;
  data_retention_years: number;
  session_timeout: number;
  enable_2fa: boolean;
  enable_audit_log: boolean;
}

export interface NotificationSettings {
  email_notifications: boolean;
  data_entry_reminders: boolean;
  approval_notifications: boolean;
  report_notifications: boolean;
}

class SettingsService {
  private baseUrl = '/settings';

  async getAll(): Promise<SystemSettings> {
    const response = await apiService.get<{ success: boolean; data: SystemSettings }>(this.baseUrl);
    return response.data;
  }

  async update(settings: Partial<SystemSettings>): Promise<SystemSettings> {
    const response = await apiService.put<{ success: boolean; data: SystemSettings }>(this.baseUrl, settings);
    return response.data;
  }

  async getNotificationSettings(): Promise<NotificationSettings> {
    const response = await apiService.get<{ success: boolean; data: NotificationSettings }>(
      `${this.baseUrl}/notifications`
    );
    return response.data;
  }

  async updateNotificationSettings(settings: NotificationSettings): Promise<NotificationSettings> {
    const response = await apiService.put<{ success: boolean; data: NotificationSettings }>(
      `${this.baseUrl}/notifications`,
      settings
    );
    return response.data;
  }
}

export const settingsService = new SettingsService();
export default settingsService;
