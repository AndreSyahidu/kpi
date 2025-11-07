import { useState, useEffect } from 'react';
import {
  Box, Typography, Card, CardContent, Grid, TextField, Button, Avatar, Divider,
  Alert, CircularProgress, Paper, IconButton, InputAdornment, Tabs, Tab,
  useTheme, useMediaQuery, Switch, List, ListItem, ListItemText,
  ListItemIcon, Chip,
} from '@mui/material';
import {
  Person as PersonIcon, Lock as LockIcon, Notifications as NotificationsIcon,
  Settings as SettingsIcon, Visibility, VisibilityOff, Save as SaveIcon,
  Business as BusinessIcon, Email as EmailIcon, Badge as BadgeIcon,
} from '@mui/icons-material';
import { useSnackbar } from 'notistack';
import { useAuthStore } from '@/store/auth.store';
import settingsService from '@/services/settings.service';
import usersService from '@/services/users.service';
import departmentsService, { Department } from '@/services/departments.service';

interface TabPanelProps {
  children?: React.ReactNode;
  index: number;
  value: number;
}

const TabPanel = ({ children, value, index }: TabPanelProps) => (
  <div role="tabpanel" hidden={value !== index}>
    {value === index && <Box sx={{ py: 3 }}>{children}</Box>}
  </div>
);

const roleColors = {
  super_admin: '#D32F2F',
  dept_head: '#1976D2',
  manager: '#388E3C',
  staff: '#757575',
};

const roleLabels = {
  super_admin: 'Super Admin',
  dept_head: 'Department Head',
  manager: 'Manager',
  staff: 'Staff',
};

export default function SettingsPage() {
  const { enqueueSnackbar } = useSnackbar();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  const user = useAuthStore((state) => state.user);

  const [activeTab, setActiveTab] = useState(0);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [department, setDepartment] = useState<Department | null>(null);

  // Profile form
  const [profileData, setProfileData] = useState({
    full_name: '',
    email: '',
  });

  // Password form
  const [passwordData, setPasswordData] = useState({
    current_password: '',
    new_password: '',
    confirm_password: '',
  });
  const [showPasswords, setShowPasswords] = useState({
    current: false,
    new: false,
    confirm: false,
  });

  // Notification preferences
  const [notificationSettings, setNotificationSettings] = useState({
    email_notifications: true,
    data_entry_reminders: true,
    approval_notifications: true,
    report_notifications: true,
  });

  useEffect(() => {
    loadUserData();
  }, []);

  const loadUserData = async () => {
    try {
      setLoading(true);

      if (user) {
        setProfileData({
          full_name: user.full_name,
          email: user.email,
        });

        // Load department if user has one
        if (user.department_id) {
          const depts = await departmentsService.getAll();
          const userDept = depts.find((d) => d.id === user.department_id);
          if (userDept) setDepartment(userDept);
        }

        // Load notification settings
        try {
          const settings = await settingsService.getNotificationSettings();
          if (settings) setNotificationSettings(settings);
        } catch (err) {
          // Settings might not exist yet, use defaults
        }
      }
    } catch (err: any) {
      enqueueSnackbar(err.response?.data?.message || 'Failed to load user data', { variant: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const handleUpdateProfile = async () => {
    if (!user) return;

    try {
      setSaving(true);
      await usersService.update(user.id, {
        full_name: profileData.full_name,
        email: profileData.email,
      });
      enqueueSnackbar('Profile updated successfully', { variant: 'success' });

      // Update auth store
      useAuthStore.getState().setUser({
        ...user,
        full_name: profileData.full_name,
        email: profileData.email,
      });
    } catch (err: any) {
      enqueueSnackbar(err.response?.data?.message || 'Failed to update profile', { variant: 'error' });
    } finally {
      setSaving(false);
    }
  };

  const handleChangePassword = async () => {
    if (!user) return;

    if (passwordData.new_password !== passwordData.confirm_password) {
      enqueueSnackbar('New passwords do not match', { variant: 'error' });
      return;
    }

    if (passwordData.new_password.length < 8) {
      enqueueSnackbar('Password must be at least 8 characters', { variant: 'error' });
      return;
    }

    try {
      setSaving(true);
      await usersService.update(user.id, {
        password: passwordData.new_password,
      });
      enqueueSnackbar('Password changed successfully', { variant: 'success' });
      setPasswordData({
        current_password: '',
        new_password: '',
        confirm_password: '',
      });
    } catch (err: any) {
      enqueueSnackbar(err.response?.data?.message || 'Failed to change password', { variant: 'error' });
    } finally {
      setSaving(false);
    }
  };

  const handleUpdateNotifications = async () => {
    try {
      setSaving(true);
      await settingsService.updateNotificationSettings(notificationSettings);
      enqueueSnackbar('Notification settings updated', { variant: 'success' });
    } catch (err: any) {
      enqueueSnackbar(err.response?.data?.message || 'Failed to update settings', { variant: 'error' });
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: 400 }}>
        <CircularProgress size={isMobile ? 40 : 60} />
      </Box>
    );
  }

  return (
    <Box sx={{ pb: isMobile ? 4 : 0 }}>
      <Box sx={{ mb: 3 }}>
        <Typography variant={isMobile ? 'h5' : 'h4'} sx={{ fontWeight: 700, mb: 0.5 }}>
          <SettingsIcon sx={{ mr: 1, verticalAlign: 'middle', fontSize: 'inherit' }} />
          Settings
        </Typography>
        <Typography variant="body2" color="text.secondary">
          Manage your account settings and preferences
        </Typography>
      </Box>

      {/* User Info Card */}
      <Card elevation={0} sx={{ mb: 3, border: '1px solid', borderColor: 'divider' }}>
        <CardContent sx={{ p: isMobile ? 2 : 3 }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
            <Avatar
              sx={{
                width: isMobile ? 64 : 80,
                height: isMobile ? 64 : 80,
                bgcolor: user?.role ? (roleColors as any)[user.role] : '#757575',
                fontSize: isMobile ? 24 : 32,
                fontWeight: 700,
              }}
            >
              {user?.full_name?.charAt(0).toUpperCase()}
            </Avatar>
            <Box sx={{ flex: 1 }}>
              <Typography variant={isMobile ? 'h6' : 'h5'} sx={{ fontWeight: 600, mb: 0.5 }}>
                {user?.full_name}
              </Typography>
              <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, alignItems: 'center' }}>
                <Chip
                  label={user?.role ? roleLabels[user.role] : 'Unknown'}
                  size="small"
                  sx={{
                    bgcolor: user?.role ? `${roleColors[user.role]}20` : '#75757520',
                    color: user?.role ? (roleColors as any)[user.role] : '#757575',
                    fontWeight: 600,
                  }}
                />
                {department && (
                  <Chip
                    icon={<BusinessIcon fontSize="small" />}
                    label={department.name}
                    size="small"
                    variant="outlined"
                  />
                )}
                <Chip
                  label={user?.is_active ? 'Active' : 'Inactive'}
                  size="small"
                  color={user?.is_active ? 'success' : 'default'}
                />
              </Box>
            </Box>
          </Box>
        </CardContent>
      </Card>

      {/* Settings Tabs */}
      <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider' }}>
        <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
          <Tabs
            value={activeTab}
            onChange={(_, newValue) => setActiveTab(newValue)}
            variant={isMobile ? 'fullWidth' : 'standard'}
            sx={{ px: isMobile ? 0 : 2 }}
          >
            <Tab icon={<PersonIcon />} label="Profile" iconPosition="start" />
            <Tab icon={<LockIcon />} label="Security" iconPosition="start" />
            <Tab icon={<NotificationsIcon />} label="Notifications" iconPosition="start" />
          </Tabs>
        </Box>

        <CardContent sx={{ p: isMobile ? 2 : 3 }}>
          {/* Profile Tab */}
          <TabPanel value={activeTab} index={0}>
            <Typography variant="h6" sx={{ fontWeight: 600, mb: 3 }}>
              Personal Information
            </Typography>
            <Grid container spacing={3}>
              <Grid item xs={12}>
                <TextField
                  fullWidth
                  label="Username"
                  value={user?.username || ''}
                  disabled
                  InputProps={{
                    startAdornment: <BadgeIcon sx={{ mr: 1, color: 'text.secondary' }} />,
                  }}
                  helperText="Username cannot be changed"
                />
              </Grid>
              <Grid item xs={12}>
                <TextField
                  fullWidth
                  label="Full Name"
                  value={profileData.full_name}
                  onChange={(e) => setProfileData({ ...profileData, full_name: e.target.value })}
                  required
                />
              </Grid>
              <Grid item xs={12}>
                <TextField
                  fullWidth
                  label="Email Address"
                  type="email"
                  value={profileData.email}
                  onChange={(e) => setProfileData({ ...profileData, email: e.target.value })}
                  required
                  InputProps={{
                    startAdornment: <EmailIcon sx={{ mr: 1, color: 'text.secondary' }} />,
                  }}
                />
              </Grid>
              <Grid item xs={12}>
                <TextField
                  fullWidth
                  label="Department"
                  value={department?.name || 'Not assigned'}
                  disabled
                  InputProps={{
                    startAdornment: <BusinessIcon sx={{ mr: 1, color: 'text.secondary' }} />,
                  }}
                  helperText="Contact your administrator to change department"
                />
              </Grid>
              <Grid item xs={12}>
                <Button
                  variant="contained"
                  startIcon={<SaveIcon />}
                  onClick={handleUpdateProfile}
                  disabled={saving || !profileData.full_name || !profileData.email}
                  size={isMobile ? 'medium' : 'large'}
                >
                  {saving ? 'Saving...' : 'Save Changes'}
                </Button>
              </Grid>
            </Grid>
          </TabPanel>

          {/* Security Tab */}
          <TabPanel value={activeTab} index={1}>
            <Typography variant="h6" sx={{ fontWeight: 600, mb: 1 }}>
              Change Password
            </Typography>
            <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
              Choose a strong password with at least 8 characters
            </Typography>
            <Grid container spacing={3}>
              <Grid item xs={12}>
                <TextField
                  fullWidth
                  label="Current Password"
                  type={showPasswords.current ? 'text' : 'password'}
                  value={passwordData.current_password}
                  onChange={(e) => setPasswordData({ ...passwordData, current_password: e.target.value })}
                  InputProps={{
                    endAdornment: (
                      <InputAdornment position="end">
                        <IconButton
                          onClick={() => setShowPasswords({ ...showPasswords, current: !showPasswords.current })}
                          edge="end"
                        >
                          {showPasswords.current ? <VisibilityOff /> : <Visibility />}
                        </IconButton>
                      </InputAdornment>
                    ),
                  }}
                />
              </Grid>
              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="New Password"
                  type={showPasswords.new ? 'text' : 'password'}
                  value={passwordData.new_password}
                  onChange={(e) => setPasswordData({ ...passwordData, new_password: e.target.value })}
                  InputProps={{
                    endAdornment: (
                      <InputAdornment position="end">
                        <IconButton
                          onClick={() => setShowPasswords({ ...showPasswords, new: !showPasswords.new })}
                          edge="end"
                        >
                          {showPasswords.new ? <VisibilityOff /> : <Visibility />}
                        </IconButton>
                      </InputAdornment>
                    ),
                  }}
                  helperText="Minimum 8 characters"
                />
              </Grid>
              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="Confirm New Password"
                  type={showPasswords.confirm ? 'text' : 'password'}
                  value={passwordData.confirm_password}
                  onChange={(e) => setPasswordData({ ...passwordData, confirm_password: e.target.value })}
                  InputProps={{
                    endAdornment: (
                      <InputAdornment position="end">
                        <IconButton
                          onClick={() => setShowPasswords({ ...showPasswords, confirm: !showPasswords.confirm })}
                          edge="end"
                        >
                          {showPasswords.confirm ? <VisibilityOff /> : <Visibility />}
                        </IconButton>
                      </InputAdornment>
                    ),
                  }}
                  error={
                    passwordData.confirm_password !== '' &&
                    passwordData.new_password !== passwordData.confirm_password
                  }
                  helperText={
                    passwordData.confirm_password !== '' &&
                    passwordData.new_password !== passwordData.confirm_password
                      ? 'Passwords do not match'
                      : ''
                  }
                />
              </Grid>
              <Grid item xs={12}>
                <Alert severity="info" sx={{ mb: 2 }}>
                  <strong>Password Requirements:</strong>
                  <ul style={{ margin: '8px 0 0', paddingLeft: '20px' }}>
                    <li>At least 8 characters long</li>
                    <li>Mix of letters, numbers, and symbols recommended</li>
                    <li>Avoid common words or patterns</li>
                  </ul>
                </Alert>
                <Button
                  variant="contained"
                  startIcon={<LockIcon />}
                  onClick={handleChangePassword}
                  disabled={
                    saving ||
                    !passwordData.new_password ||
                    !passwordData.confirm_password ||
                    passwordData.new_password !== passwordData.confirm_password
                  }
                  size={isMobile ? 'medium' : 'large'}
                >
                  {saving ? 'Changing...' : 'Change Password'}
                </Button>
              </Grid>
            </Grid>
          </TabPanel>

          {/* Notifications Tab */}
          <TabPanel value={activeTab} index={2}>
            <Typography variant="h6" sx={{ fontWeight: 600, mb: 1 }}>
              Notification Preferences
            </Typography>
            <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
              Choose which notifications you want to receive
            </Typography>
            <Paper elevation={0} sx={{ border: '1px solid', borderColor: 'divider' }}>
              <List>
                <ListItem>
                  <ListItemIcon>
                    <EmailIcon color="primary" />
                  </ListItemIcon>
                  <ListItemText
                    primary="Email Notifications"
                    secondary="Receive notifications via email"
                  />
                  <Switch
                    checked={notificationSettings.email_notifications}
                    onChange={(e) =>
                      setNotificationSettings({
                        ...notificationSettings,
                        email_notifications: e.target.checked,
                      })
                    }
                  />
                </ListItem>
                <Divider />
                <ListItem>
                  <ListItemIcon>
                    <NotificationsIcon color="warning" />
                  </ListItemIcon>
                  <ListItemText
                    primary="Data Entry Reminders"
                    secondary="Get reminded when KPI data entry is due"
                  />
                  <Switch
                    checked={notificationSettings.data_entry_reminders}
                    onChange={(e) =>
                      setNotificationSettings({
                        ...notificationSettings,
                        data_entry_reminders: e.target.checked,
                      })
                    }
                  />
                </ListItem>
                <Divider />
                <ListItem>
                  <ListItemIcon>
                    <NotificationsIcon color="success" />
                  </ListItemIcon>
                  <ListItemText
                    primary="Approval Notifications"
                    secondary="Get notified about approval status changes"
                  />
                  <Switch
                    checked={notificationSettings.approval_notifications}
                    onChange={(e) =>
                      setNotificationSettings({
                        ...notificationSettings,
                        approval_notifications: e.target.checked,
                      })
                    }
                  />
                </ListItem>
                <Divider />
                <ListItem>
                  <ListItemIcon>
                    <NotificationsIcon color="info" />
                  </ListItemIcon>
                  <ListItemText
                    primary="Report Notifications"
                    secondary="Receive notifications about generated reports"
                  />
                  <Switch
                    checked={notificationSettings.report_notifications}
                    onChange={(e) =>
                      setNotificationSettings({
                        ...notificationSettings,
                        report_notifications: e.target.checked,
                      })
                    }
                  />
                </ListItem>
              </List>
            </Paper>
            <Box sx={{ mt: 3 }}>
              <Button
                variant="contained"
                startIcon={<SaveIcon />}
                onClick={handleUpdateNotifications}
                disabled={saving}
                size={isMobile ? 'medium' : 'large'}
              >
                {saving ? 'Saving...' : 'Save Preferences'}
              </Button>
            </Box>
          </TabPanel>
        </CardContent>
      </Card>
    </Box>
  );
}
