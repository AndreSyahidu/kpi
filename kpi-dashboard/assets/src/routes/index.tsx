import { Routes, Route, Navigate } from 'react-router-dom';
import { useAuthStore } from '@/store/authStore';
import LoginPage from '@/pages/auth/LoginPage';
import DashboardLayout from '@/layouts/DashboardLayout';
import OverviewPage from '@/pages/dashboard/OverviewPage';
import UsersPage from '@/pages/users/UsersPage';
import DepartmentsPage from '@/pages/departments/DepartmentsPage';
import KPIsPage from '@/pages/kpis/KPIsPage';
import DataEntryPage from '@/pages/data-entry/DataEntryPage';
import ApprovalsPage from '@/pages/approvals/ApprovalsPage';
import ReportsPage from '@/pages/reports/ReportsPage';
import AnalyticsPage from '@/pages/analytics/AnalyticsPage';
import NotificationsPage from '@/pages/notifications/NotificationsPage';
import SettingsPage from '@/pages/settings/SettingsPage';

function PrivateRoute({ children }: { children: React.ReactNode }) {
  const isAuthenticated = useAuthStore((state) => state.isAuthenticated);
  return isAuthenticated ? <>{children}</> : <Navigate to="/login" replace />;
}

export default function AppRoutes() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/" element={<PrivateRoute><DashboardLayout /></PrivateRoute>}>
        <Route index element={<OverviewPage />} />
        <Route path="users" element={<UsersPage />} />
        <Route path="departments" element={<DepartmentsPage />} />
        <Route path="kpis" element={<KPIsPage />} />
        <Route path="data-entry" element={<DataEntryPage />} />
        <Route path="approvals" element={<ApprovalsPage />} />
        <Route path="reports" element={<ReportsPage />} />
        <Route path="analytics" element={<AnalyticsPage />} />
        <Route path="notifications" element={<NotificationsPage />} />
        <Route path="settings" element={<SettingsPage />} />
      </Route>
    </Routes>
  );
}
