import apiService from './api.service';

export interface Notification {
  id: number;
  user_id: number;
  type: 'reminder' | 'approval' | 'alert' | 'info' | 'success' | 'warning' | 'error';
  severity: 'info' | 'warning' | 'critical' | 'success';
  title: string;
  message: string;
  action_url: string | null;
  related_entity_type: string | null;
  related_entity_id: number | null;
  is_read: boolean;
  read_at: string | null;
  sent_via_email: boolean;
  email_sent_at: string | null;
  created_at: string;
}

class NotificationsService {
  private baseUrl = '/notifications';

  async getAll(): Promise<Notification[]> {
    const response = await apiService.get<{ success: boolean; data: Notification[] }>(this.baseUrl);
    return response.data;
  }

  async markAsRead(id: number): Promise<void> {
    await apiService.post(`${this.baseUrl}/${id}/read`);
  }

  async markAllAsRead(): Promise<void> {
    await apiService.post(`${this.baseUrl}/mark-all-read`);
  }

  async getUnreadCount(): Promise<number> {
    const response = await apiService.get<{ success: boolean; data: { count: number } }>(`${this.baseUrl}/unread-count`);
    return response.data.count;
  }

  async getPreferences(): Promise<any> {
    const response = await apiService.get<{ success: boolean; data: any }>(`${this.baseUrl}/preferences`);
    return response.data;
  }

  async updatePreferences(preferences: any): Promise<void> {
    await apiService.put(`${this.baseUrl}/preferences`, preferences);
  }

  async delete(id: number): Promise<void> {
    await apiService.delete(`${this.baseUrl}/${id}`);
  }
}

export const notificationsService = new NotificationsService();
export default notificationsService;
