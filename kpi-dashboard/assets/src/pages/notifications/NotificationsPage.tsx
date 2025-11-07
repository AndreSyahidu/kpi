import { useState, useEffect } from 'react';
import {
  Box, Typography, Card, CardContent, Grid, Button, Paper, Chip, IconButton,
  Alert, CircularProgress, useTheme, useMediaQuery, Avatar, Tab, Tabs, Badge,
  List, ListItem, ListItemAvatar, ListItemText, ListItemSecondaryAction, Divider,
} from '@mui/material';
import {
  Notifications as NotificationsIcon, CheckCircle as CheckIcon,
  Info as InfoIcon, Warning as WarningIcon, Error as ErrorIcon,
  Delete as DeleteIcon, DoneAll as MarkAllIcon, Assessment as AssessmentIcon,
} from '@mui/icons-material';
import { useSnackbar } from 'notistack';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import notificationsService, { Notification } from '@/services/notifications.service';

dayjs.extend(relativeTime);

const typeIcons = {
  info: InfoIcon,
  success: CheckIcon,
  warning: WarningIcon,
  error: ErrorIcon,
};

const typeColors = {
  info: '#1976D2',
  success: '#2E7D32',
  warning: '#ED6C02',
  error: '#D32F2F',
};

interface TabPanelProps {
  children?: React.ReactNode;
  index: number;
  value: number;
}

const TabPanel = ({ children, value, index }: TabPanelProps) => (
  <div role="tabpanel" hidden={value !== index}>
    {value === index && <Box sx={{ mt: 2 }}>{children}</Box>}
  </div>
);

export default function NotificationsPage() {
  const { enqueueSnackbar } = useSnackbar();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));

  const [activeTab, setActiveTab] = useState(0);
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [loading, setLoading] = useState(true);
  const [processing, setProcessing] = useState<number | null>(null);

  useEffect(() => {
    loadNotifications();
  }, []);

  const loadNotifications = async () => {
    try {
      setLoading(true);
      const data = await notificationsService.getAll();
      setNotifications(data);
    } catch (err: any) {
      const errorMsg = err.response?.data?.message || 'Failed to load notifications';
      enqueueSnackbar(errorMsg, { variant: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const handleMarkAsRead = async (id: number) => {
    try {
      setProcessing(id);
      await notificationsService.markAsRead(id);
      setNotifications((prev) =>
        prev.map((n) => (n.id === id ? { ...n, read_at: new Date().toISOString() } : n))
      );
      enqueueSnackbar('Notification marked as read', { variant: 'success' });
    } catch (err: any) {
      enqueueSnackbar(err.response?.data?.message || 'Failed to mark as read', { variant: 'error' });
    } finally {
      setProcessing(null);
    }
  };

  const handleMarkAllAsRead = async () => {
    try {
      setProcessing(0);
      await notificationsService.markAllAsRead();
      setNotifications((prev) =>
        prev.map((n) => ({ ...n, read_at: n.read_at || new Date().toISOString() }))
      );
      enqueueSnackbar('All notifications marked as read', { variant: 'success' });
    } catch (err: any) {
      enqueueSnackbar(err.response?.data?.message || 'Failed to mark all as read', { variant: 'error' });
    } finally {
      setProcessing(null);
    }
  };

  const handleDelete = async (id: number) => {
    try {
      setProcessing(id);
      await notificationsService.delete(id);
      setNotifications((prev) => prev.filter((n) => n.id !== id));
      enqueueSnackbar('Notification deleted', { variant: 'success' });
    } catch (err: any) {
      enqueueSnackbar(err.response?.data?.message || 'Failed to delete', { variant: 'error' });
    } finally {
      setProcessing(null);
    }
  };

  const unreadNotifications = notifications.filter((n) => !n.read_at);
  const readNotifications = notifications.filter((n) => n.read_at);

  const renderNotificationItem = (notification: Notification) => {
    const IconComponent = typeIcons[notification.type];
    const isUnread = !notification.read_at;

    return (
      <Paper
        key={notification.id}
        elevation={0}
        sx={{
          p: 2,
          mb: 1,
          border: '1px solid',
          borderColor: 'divider',
          borderLeft: `4px solid ${typeColors[notification.type]}`,
          bgcolor: isUnread ? `${typeColors[notification.type]}05` : 'transparent',
          transition: 'all 0.2s',
          '&:hover': {
            borderColor: typeColors[notification.type],
            boxShadow: `0 2px 8px ${typeColors[notification.type]}30`,
          },
        }}
      >
        <Box sx={{ display: 'flex', alignItems: 'flex-start', gap: 2 }}>
          <Avatar
            sx={{
              bgcolor: `${typeColors[notification.type]}20`,
              color: typeColors[notification.type],
              width: 40,
              height: 40,
            }}
          >
            <IconComponent fontSize="small" />
          </Avatar>

          <Box sx={{ flex: 1, minWidth: 0 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 0.5 }}>
              <Typography variant="subtitle2" sx={{ fontWeight: isUnread ? 700 : 600 }}>
                {notification.title}
              </Typography>
              {isUnread && <Chip label="New" size="small" color="primary" sx={{ height: 20 }} />}
            </Box>

            <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
              {notification.message}
            </Typography>

            <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 1 }}>
              <Typography variant="caption" color="text.secondary">
                {dayjs(notification.created_at).fromNow()}
              </Typography>

              <Box sx={{ display: 'flex', gap: 1 }}>
                {isUnread && (
                  <Button
                    size="small"
                    variant="text"
                    onClick={() => handleMarkAsRead(notification.id)}
                    disabled={processing === notification.id}
                  >
                    Mark as read
                  </Button>
                )}
                <IconButton
                  size="small"
                  color="error"
                  onClick={() => handleDelete(notification.id)}
                  disabled={processing === notification.id}
                >
                  <DeleteIcon fontSize="small" />
                </IconButton>
              </Box>
            </Box>
          </Box>
        </Box>
      </Paper>
    );
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
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', mb: 2 }}>
          <Box>
            <Typography variant={isMobile ? 'h5' : 'h4'} sx={{ fontWeight: 700, mb: 0.5 }}>
              <NotificationsIcon sx={{ mr: 1, verticalAlign: 'middle', fontSize: 'inherit' }} />
              Notifications
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Stay updated with your KPI activities
            </Typography>
          </Box>
          {unreadNotifications.length > 0 && !isMobile && (
            <Button
              variant="outlined"
              startIcon={<MarkAllIcon />}
              onClick={handleMarkAllAsRead}
              disabled={processing === 0}
            >
              Mark all as read
            </Button>
          )}
        </Box>
      </Box>

      <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider' }}>
        <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
          <Tabs
            value={activeTab}
            onChange={(_, newValue) => setActiveTab(newValue)}
            variant={isMobile ? 'fullWidth' : 'standard'}
          >
            <Tab
              icon={
                <Badge badgeContent={unreadNotifications.length} color="error">
                  <NotificationsIcon />
                </Badge>
              }
              label="Unread"
              iconPosition="start"
            />
            <Tab icon={<AssessmentIcon />} label="All" iconPosition="start" />
          </Tabs>
        </Box>

        <CardContent sx={{ p: isMobile ? 2 : 3 }}>
          {/* Unread Tab */}
          <TabPanel value={activeTab} index={0}>
            {unreadNotifications.length === 0 ? (
              <Box sx={{ textAlign: 'center', py: isMobile ? 4 : 8 }}>
                <CheckIcon sx={{ fontSize: isMobile ? 60 : 80, color: '#2E7D32', mb: 2 }} />
                <Typography variant={isMobile ? 'h6' : 'h5'} color="text.secondary" sx={{ mb: 1 }}>
                  All caught up!
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  You have no unread notifications
                </Typography>
              </Box>
            ) : (
              <>
                {isMobile && unreadNotifications.length > 0 && (
                  <Button
                    fullWidth
                    variant="outlined"
                    startIcon={<MarkAllIcon />}
                    onClick={handleMarkAllAsRead}
                    disabled={processing === 0}
                    sx={{ mb: 2 }}
                  >
                    Mark all as read
                  </Button>
                )}
                <Box>{unreadNotifications.map((notification) => renderNotificationItem(notification))}</Box>
              </>
            )}
          </TabPanel>

          {/* All Tab */}
          <TabPanel value={activeTab} index={1}>
            {notifications.length === 0 ? (
              <Box sx={{ textAlign: 'center', py: isMobile ? 4 : 8 }}>
                <NotificationsIcon sx={{ fontSize: isMobile ? 60 : 80, color: 'text.secondary', mb: 2 }} />
                <Typography variant={isMobile ? 'h6' : 'h5'} color="text.secondary" sx={{ mb: 1 }}>
                  No notifications yet
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  You'll receive notifications about your KPI activities here
                </Typography>
              </Box>
            ) : (
              <Box>
                {notifications.map((notification) => renderNotificationItem(notification))}
              </Box>
            )}
          </TabPanel>

          {notifications.length > 0 && (
            <Box sx={{ mt: 3, pt: 2, borderTop: 1, borderColor: 'divider' }}>
              <Typography variant="body2" color="text.secondary">
                Total: {notifications.length} notifications ({unreadNotifications.length} unread)
              </Typography>
            </Box>
          )}
        </CardContent>
      </Card>
    </Box>
  );
}
