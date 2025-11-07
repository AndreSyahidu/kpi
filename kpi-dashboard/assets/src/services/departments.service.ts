import apiService from './api.service';

export interface Department {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  parent_id: number | null;
  color_code: string;
  icon_class: string | null;
  logo_url: string | null;
  sort_order: number;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  created_by: number | null;
}

export interface CreateDepartmentData {
  name: string;
  slug?: string;
  description?: string;
  parent_id?: number;
  color_code?: string;
  icon_class?: string;
  logo_url?: string;
  sort_order?: number;
  is_active?: boolean;
}

export interface UpdateDepartmentData extends Partial<CreateDepartmentData> {}

class DepartmentsService {
  private baseUrl = '/departments';

  async getAll(): Promise<Department[]> {
    const response = await apiService.get<{ success: boolean; data: Department[] }>(this.baseUrl);
    return response.data;
  }

  async getById(id: number): Promise<Department> {
    const response = await apiService.get<{ success: boolean; data: Department }>(`${this.baseUrl}/${id}`);
    return response.data;
  }

  async create(data: CreateDepartmentData): Promise<Department> {
    const response = await apiService.post<{ success: boolean; data: Department }>(this.baseUrl, data);
    return response.data;
  }

  async update(id: number, data: UpdateDepartmentData): Promise<Department> {
    const response = await apiService.put<{ success: boolean; data: Department }>(`${this.baseUrl}/${id}`, data);
    return response.data;
  }

  async delete(id: number): Promise<void> {
    await apiService.delete(`${this.baseUrl}/${id}`);
  }

  async getHeads(id: number): Promise<any[]> {
    const response = await apiService.get<{ success: boolean; data: any[] }>(`${this.baseUrl}/${id}/heads`);
    return response.data;
  }

  async getHierarchy(): Promise<any[]> {
    const response = await apiService.get<{ success: boolean; data: any[] }>(`${this.baseUrl}/hierarchy`);
    return response.data;
  }
}

export const departmentsService = new DepartmentsService();
export default departmentsService;
