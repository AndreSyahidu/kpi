import apiService from './api.service';

export interface ReportParams {
  type: 'department' | 'executive' | 'kpi';
  department_id?: number;
  kpi_id?: number;
  period_start?: string;
  period_end?: string;
  format?: 'json' | 'pdf' | 'excel';
}

class ReportsService {
  private baseUrl = '/reports';

  async generateDepartmentReport(departmentId: number, params?: any): Promise<any> {
    const response = await apiService.get<{ success: boolean; data: any }>(`${this.baseUrl}/department`, {
      department_id: departmentId,
      ...params,
    });
    return response.data;
  }

  async generateExecutiveReport(params?: any): Promise<any> {
    const response = await apiService.get<{ success: boolean; data: any }>(`${this.baseUrl}/executive`, params);
    return response.data;
  }

  async generateKPIReport(kpiId: number, params?: any): Promise<any> {
    const response = await apiService.get<{ success: boolean; data: any }>(`${this.baseUrl}/kpi`, {
      kpi_id: kpiId,
      ...params,
    });
    return response.data;
  }
}

export const reportsService = new ReportsService();
export default reportsService;
