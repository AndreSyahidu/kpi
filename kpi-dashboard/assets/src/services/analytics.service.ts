import apiService from './api.service';

export interface AnalyticsOverview {
  total_users: number;
  active_users: number;
  total_departments: number;
  total_kpis: number;
  active_kpis: number;
  pending_approvals: number;
  total_data_entries: number;
  completion_rate: number;
}

class AnalyticsService {
  private baseUrl = '/analytics';

  async getOverview(): Promise<AnalyticsOverview> {
    const response = await apiService.get<{ success: boolean; data: AnalyticsOverview }>(`${this.baseUrl}/overview`);
    return response.data;
  }

  async getBestPerformers(limit = 10): Promise<any[]> {
    const response = await apiService.get<{ success: boolean; data: any[] }>(`${this.baseUrl}/best-performers`, { limit });
    return response.data;
  }

  async getLowPerformers(limit = 10): Promise<any[]> {
    const response = await apiService.get<{ success: boolean; data: any[] }>(`${this.baseUrl}/low-performers`, { limit });
    return response.data;
  }

  async getTrend(kpiId: number, period = 'monthly'): Promise<any> {
    const response = await apiService.get<{ success: boolean; data: any }>(`${this.baseUrl}/trend`, { kpi_id: kpiId, period });
    return response.data;
  }

  async getComparison(params: any): Promise<any> {
    const response = await apiService.get<{ success: boolean; data: any }>(`${this.baseUrl}/comparison`, params);
    return response.data;
  }

  async getInsights(): Promise<any[]> {
    const response = await apiService.get<{ success: boolean; data: any[] }>(`${this.baseUrl}/insights`);
    return response.data;
  }
}

export const analyticsService = new AnalyticsService();
export default analyticsService;
