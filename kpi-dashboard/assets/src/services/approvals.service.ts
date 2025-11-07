import apiService from './api.service';
import { KPIData } from './data.service';

export interface Approval extends KPIData {
  approval_status: 'pending' | 'approved' | 'rejected';
}

class ApprovalsService {
  private baseUrl = '/approvals';

  async getAll(params?: any): Promise<Approval[]> {
    const response = await apiService.get<{ success: boolean; data: Approval[] }>(this.baseUrl, params);
    return response.data;
  }

  async approve(id: number, notes?: string): Promise<void> {
    await apiService.post(`${this.baseUrl}/${id}/approve`, { notes });
  }

  async reject(id: number, notes: string): Promise<void> {
    await apiService.post(`${this.baseUrl}/${id}/reject`, { notes });
  }

  async bulkApprove(ids: number[], notes?: string): Promise<void> {
    await apiService.post(`${this.baseUrl}/bulk-approve`, { ids, notes });
  }
}

export const approvalsService = new ApprovalsService();
export default approvalsService;
