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
}

export const settingsService = new SettingsService();
export default settingsService;
