import { useState } from 'react';
import { Outlet } from 'react-router-dom';
import { Box, AppBar, Toolbar, IconButton, Typography, Drawer, List, ListItem, ListItemIcon, ListItemText, Avatar, Menu, MenuItem } from '@mui/material';
import { Menu as MenuIcon, Dashboard, People, Business, Assessment, DataUsage, CheckCircle, Description, Insights, Notifications, Settings, Logout, DarkMode, LightMode } from '@mui/icons-material';
import { useAuthStore } from '@/store/authStore';
import { useAppStore } from '@/store/appStore';
import { useNavigate } from 'react-router-dom';
import { APP_CONFIG } from '@/config/api.config';

const DRAWER_WIDTH = 260;

const menuItems = [
  { path: '/', label: 'Dashboard', icon: <Dashboard /> },
  { path: '/users', label: 'Users', icon: <People /> },
  { path: '/departments', label: 'Departments', icon: <Business /> },
  { path: '/kpis', label: 'KPIs', icon: <Assessment /> },
  { path: '/data-entry', label: 'Data Entry', icon: <DataUsage /> },
  { path: '/approvals', label: 'Approvals', icon: <CheckCircle /> },
  { path: '/reports', label: 'Reports', icon: <Description /> },
  { path: '/analytics', label: 'Analytics', icon: <Insights /> },
  { path: '/notifications', label: 'Notifications', icon: <Notifications /> },
  { path: '/settings', label: 'Settings', icon: <Settings /> },
];

export default function DashboardLayout() {
  const navigate = useNavigate();
  const user = useAuthStore((state) => state.user);
  const logout = useAuthStore((state) => state.logout);
  const { darkMode, toggleDarkMode, sidebarOpen, toggleSidebar } = useAppStore();
  const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <Box sx={{ display: 'flex', minHeight: '100vh' }}>
      <AppBar position="fixed" sx={{ zIndex: (theme) => theme.zIndex.drawer + 1 }}>
        <Toolbar>
          <IconButton color="inherit" edge="start" onClick={toggleSidebar} sx={{ mr: 2 }}>
            <MenuIcon />
          </IconButton>
          <Typography variant="h6" noWrap component="div" sx={{ flexGrow: 1 }}>
            {APP_CONFIG.companyName} - KPI Dashboard
          </Typography>
          <IconButton color="inherit" onClick={toggleDarkMode}>
            {darkMode ? <LightMode /> : <DarkMode />}
          </IconButton>
          <IconButton color="inherit" onClick={(e) => setAnchorEl(e.currentTarget)}>
            <Avatar sx={{ width: 32, height: 32 }}>{user?.full_name?.[0]}</Avatar>
          </IconButton>
          <Menu anchorEl={anchorEl} open={Boolean(anchorEl)} onClose={() => setAnchorEl(null)}>
            <MenuItem onClick={handleLogout}><Logout sx={{ mr: 1 }} /> Logout</MenuItem>
          </Menu>
        </Toolbar>
      </AppBar>

      <Drawer variant="persistent" open={sidebarOpen} sx={{ width: DRAWER_WIDTH, flexShrink: 0, '& .MuiDrawer-paper': { width: DRAWER_WIDTH, boxSizing: 'border-box' } }}>
        <Toolbar />
        <Box sx={{ overflow: 'auto', mt: 2 }}>
          <List>
            {menuItems.map((item) => (
              <ListItem button key={item.path} onClick={() => navigate(item.path)}>
                <ListItemIcon>{item.icon}</ListItemIcon>
                <ListItemText primary={item.label} />
              </ListItem>
            ))}
          </List>
        </Box>
      </Drawer>

      <Box component="main" sx={{ flexGrow: 1, p: 3, mt: 8, ml: sidebarOpen ? `${DRAWER_WIDTH}px` : 0, transition: 'margin 0.3s' }}>
        <Outlet />
      </Box>
    </Box>
  );
}
