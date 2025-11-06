import apiService from './api.service';
import { AuthResponse, ApiResponse, User } from '@/types';

export const authService = {
  login: (username: string, password: string) =>
    apiService.post<ApiResponse<AuthResponse>>('/auth/login', { username, password }),

  logout: () => apiService.post('/auth/logout'),

  refreshToken: (refreshToken: string) =>
    apiService.post<ApiResponse<AuthResponse>>('/auth/refresh', { refresh_token: refreshToken }),

  getMe: () => apiService.get<ApiResponse<User>>('/auth/me'),

  changePassword: (oldPassword: string, newPassword: string) =>
    apiService.post('/auth/change-password', { old_password: oldPassword, new_password: newPassword }),

  forgotPassword: (email: string) =>
    apiService.post('/auth/forgot-password', { email }),

  resetPassword: (userId: number, token: string, newPassword: string) =>
    apiService.post('/auth/reset-password', { user_id: userId, token, new_password: newPassword }),
};
