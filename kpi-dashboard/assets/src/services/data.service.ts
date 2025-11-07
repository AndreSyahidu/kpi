import apiService from './api.service';

export interface KPIData {
  id: number;
  kpi_id: number;
  kpi_name?: string;
  department_id: number;
  department_name?: string;
  position_id: number | null;
  position_name?: string;
  user_id: number | null;
  user_name?: string;
  period_start: string;
  period_end: string;
  value: number;
  unit: string | null;
  status: 'draft' | 'submitted' | 'approved' | 'rejected';
  notes: string | null;
  attachments: string | null;
  submitted_by: number;
  submitted_by_name?: string;
  submitted_at: string;
  reviewed_by: number | null;
  reviewed_at: string | null;
  review_notes: string | null;
  created_at: string;
  updated_at: string;
}

export interface CreateKPIDataInput {
  kpi_id: number;
  department_id?: number;
  position_id?: number;
  user_id?: number;
  period_start: string;
  period_end: string;
  value: number;
  unit?: string;
  status?: 'draft' | 'submitted';
  notes?: string;
}

export interface UpdateKPIDataInput {
  value?: number;
  status?: 'draft' | 'submitted' | 'approved' | 'rejected';
  notes?: string;
  review_notes?: string;
  period_start?: string;
  period_end?: string;
  kpi_id?: number;
}

class DataService {
  private baseUrl = '/data';

  async getAll(params?: any): Promise<KPIData[]> {
    const response = await apiService.get<{ success: boolean; data: KPIData[] }>(this.baseUrl, params);
    return response.data;
  }

  async getById(id: number): Promise<KPIData> {
    const response = await apiService.get<{ success: boolean; data: KPIData }>(`${this.baseUrl}/${id}`);
    return response.data;
  }

  async create(data: CreateKPIDataInput): Promise<KPIData> {
    const response = await apiService.post<{ success: boolean; data: KPIData }>(this.baseUrl, data);
    return response.data;
  }

  async update(id: number, data: UpdateKPIDataInput): Promise<KPIData> {
    const response = await apiService.put<{ success: boolean; data: KPIData }>(`${this.baseUrl}/${id}`, data);
    return response.data;
  }

  async delete(id: number): Promise<void> {
    await apiService.delete(`${this.baseUrl}/${id}`);
  }

  async bulkCreate(data: CreateKPIDataInput[]): Promise<KPIData[]> {
    const response = await apiService.post<{ success: boolean; data: KPIData[] }>(`${this.baseUrl}/bulk`, { entries: data });
    return response.data;
  }

  async submitForApproval(id: number): Promise<KPIData> {
    const response = await apiService.post<{ success: boolean; data: KPIData }>(`${this.baseUrl}/${id}/submit`, {});
    return response.data;
  }
}

export const dataService = new DataService();
export default dataService;
