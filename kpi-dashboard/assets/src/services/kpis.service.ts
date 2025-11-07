import apiService from './api.service';

export interface KPI {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  category: string;
  type: 'department' | 'position' | 'personal';
  metric_type: 'number' | 'percentage' | 'ratio' | 'custom' | 'boolean';
  unit: string | null;
  input_frequency: 'daily' | 'weekly' | 'monthly' | 'quarterly' | 'yearly';
  calculation_method: 'sum' | 'average' | 'count' | 'formula' | 'auto';
  formula: string | null;
  target_value: number | null;
  target_type: 'minimum' | 'maximum' | 'exact' | 'range';
  target_range_min: number | null;
  target_range_max: number | null;
  weight: number;
  chart_type: string;
  color_scheme: string | null;
  decimal_places: number;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface CreateKPIData {
  name: string;
  slug?: string;
  description?: string;
  category?: string;
  type: 'department' | 'position' | 'personal';
  metric_type: 'number' | 'percentage' | 'ratio' | 'custom' | 'boolean';
  unit?: string;
  input_frequency: 'daily' | 'weekly' | 'monthly' | 'quarterly' | 'yearly';
  calculation_method?: 'sum' | 'average' | 'count' | 'formula' | 'auto';
  formula?: string;
  target_value?: number;
  target_type?: 'minimum' | 'maximum' | 'exact' | 'range';
  target_range_min?: number;
  target_range_max?: number;
  weight?: number;
  chart_type?: string;
  decimal_places?: number;
  is_active?: boolean;
}

export interface UpdateKPIData extends Partial<CreateKPIData> {}

class KPIsService {
  private baseUrl = '/kpis';

  async getAll(): Promise<KPI[]> {
    const response = await apiService.get<{ success: boolean; data: KPI[] }>(this.baseUrl);
    return response.data;
  }

  async getById(id: number): Promise<KPI> {
    const response = await apiService.get<{ success: boolean; data: KPI }>(`${this.baseUrl}/${id}`);
    return response.data;
  }

  async create(data: CreateKPIData): Promise<KPI> {
    const response = await apiService.post<{ success: boolean; data: KPI }>(this.baseUrl, data);
    return response.data;
  }

  async update(id: number, data: UpdateKPIData): Promise<KPI> {
    const response = await apiService.put<{ success: boolean; data: KPI }>(`${this.baseUrl}/${id}`, data);
    return response.data;
  }

  async delete(id: number): Promise<void> {
    await apiService.delete(`${this.baseUrl}/${id}`);
  }

  async assign(id: number, data: any): Promise<void> {
    await apiService.post(`${this.baseUrl}/${id}/assign`, data);
  }
}

export const kpisService = new KPIsService();
export default kpisService;
