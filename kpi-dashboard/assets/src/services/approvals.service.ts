import apiService from './api.service';
import { KPIData } from './data.service';

export interface Approval extends KPIData {
  approval_status: 'pending' | 'approved' | 'rejected';
}

export interface ApprovalItem {
  id: number;
  kpi_id: number;
  kpi_name: string;
  value: number;
  unit: string;
  period_start: string;
  period_end: string;
  status: 'submitted' | 'approved' | 'rejected';
  notes: string | null;
  submitted_by: number;
  submitted_by_name: string;
  submitted_at: string;
  reviewed_by: number | null;
  reviewed_by_name: string | null;
  reviewed_at: string | null;
  review_notes: string | null;
}

class ApprovalsService {
  private baseUrl = '/approvals';

  async getAll(params?: any): Promise<Approval[]> {
    const response = await apiService.get<{ success: boolean; data: Approval[] }>(this.baseUrl, params);
    return response.data;
  }

  async getPending(): Promise<ApprovalItem[]> {
    const response = await apiService.get<{ success: boolean; data: ApprovalItem[] }>(
      `${this.baseUrl}/pending`
    );
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
