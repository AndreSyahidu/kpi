import apiService from './api.service';

export interface User {
  id: number;
  wp_user_id: number | null;
  username: string;
  email: string;
  full_name: string;
  avatar_url: string | null;
  role: 'super_admin' | 'dept_head' | 'manager' | 'staff';
  department_id: number | null;
  department_name?: string;
  position_id: number | null;
  position_name?: string;
  is_active: boolean;
  last_login: string | null;
  created_at: string;
  updated_at: string;
}

export interface CreateUserData {
  username: string;
  email: string;
  password: string;
  full_name: string;
  role: 'super_admin' | 'dept_head' | 'manager' | 'staff';
  department_id?: number;
  position_id?: number;
  is_active?: boolean;
}

export interface UpdateUserData {
  email?: string;
  full_name?: string;
  role?: 'super_admin' | 'dept_head' | 'manager' | 'staff';
  department_id?: number;
  position_id?: number;
  is_active?: boolean;
  password?: string;
}

class UsersService {
  private baseUrl = '/users';

  async getAll(): Promise<User[]> {
    const response = await apiService.get<{ success: boolean; data: User[] }>(this.baseUrl);
    return response.data;
  }

  async getById(id: number): Promise<User> {
    const response = await apiService.get<{ success: boolean; data: User }>(`${this.baseUrl}/${id}`);
    return response.data;
  }

  async create(data: CreateUserData): Promise<User> {
    const response = await apiService.post<{ success: boolean; data: User }>(this.baseUrl, data);
    return response.data;
  }

  async update(id: number, data: UpdateUserData): Promise<User> {
    const response = await apiService.put<{ success: boolean; data: User }>(`${this.baseUrl}/${id}`, data);
    return response.data;
  }

  async delete(id: number): Promise<void> {
    await apiService.delete(`${this.baseUrl}/${id}`);
  }
}

export const usersService = new UsersService();
export default usersService;
