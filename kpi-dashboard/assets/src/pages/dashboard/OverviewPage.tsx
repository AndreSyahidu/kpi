import { useState, useEffect } from 'react';
import {
  Grid,
  Card,
  CardContent,
  Typography,
  Box,
  CircularProgress,
  LinearProgress,
  Avatar,
  Chip,
  Alert,
  Paper,
  useTheme,
  useMediaQuery,
} from '@mui/material';
import {
  People,
  Business,
  Assessment,
  PendingActions,
  TrendingUp,
  TrendingDown,
  CheckCircle,
  Schedule,
} from '@mui/icons-material';
import { useSnackbar } from 'notistack';
import analyticsService, { AnalyticsOverview } from '@/services/analytics.service';
import departmentsService, { Department } from '@/services/departments.service';

interface StatCardProps {
  title: string;
  value: string | number;
  subtitle?: string;
  icon: React.ReactNode;
  color: string;
  trend?: number;
  loading?: boolean;
}

const StatCard = ({ title, value, subtitle, icon, color, trend, loading }: StatCardProps) => {
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));

  return (
    <Card
      elevation={0}
      sx={{
        height: '100%',
        background: `linear-gradient(135deg, ${color}15 0%, ${color}05 100%)`,
        border: `1px solid ${color}30`,
        transition: 'all 0.3s ease',
        '&:hover': {
          transform: 'translateY(-4px)',
          boxShadow: `0 8px 24px ${color}40`,
          borderColor: color,
        }
      }}
    >
      <CardContent>
        <Box sx={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between' }}>
          <Box sx={{ flex: 1 }}>
            <Typography color="text.secondary" variant="body2" sx={{ fontWeight: 500, mb: 1 }}>
              {title}
            </Typography>
            {loading ? (
              <CircularProgress size={24} sx={{ color }} />
            ) : (
              <>
                <Typography variant={isMobile ? 'h5' : 'h4'} sx={{ fontWeight: 700, color, mb: 0.5 }}>
                  {value}
                </Typography>
                {subtitle && (
                  <Typography variant="caption" color="text.secondary">
                    {subtitle}
                  </Typography>
                )}
                {trend !== undefined && (
                  <Box sx={{ display: 'flex', alignItems: 'center', mt: 1 }}>
                    {trend > 0 ? (
                      <TrendingUp sx={{ fontSize: 16, color: '#2E7D32', mr: 0.5 }} />
                    ) : (
                      <TrendingDown sx={{ fontSize: 16, color: '#D32F2F', mr: 0.5 }} />
                    )}
                    <Typography
                      variant="caption"
                      sx={{
                        color: trend > 0 ? '#2E7D32' : '#D32F2F',
                        fontWeight: 600
                      }}
                    >
                      {Math.abs(trend)}% vs last month
                    </Typography>
                  </Box>
                )}
              </>
            )}
          </Box>
          <Avatar
            sx={{
              bgcolor: color,
              width: isMobile ? 48 : 56,
              height: isMobile ? 48 : 56,
              boxShadow: `0 4px 12px ${color}60`,
            }}
          >
            {icon}
          </Avatar>
        </Box>
      </CardContent>
    </Card>
  );
};

export default function OverviewPage() {
  const { enqueueSnackbar } = useSnackbar();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));

  const [loading, setLoading] = useState(true);
  const [analytics, setAnalytics] = useState<AnalyticsOverview | null>(null);
  const [departments, setDepartments] = useState<Department[]>([]);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);
      setError(null);

      // Load analytics and departments in parallel
      const [analyticsData, departmentsData] = await Promise.all([
        analyticsService.getOverview().catch(() => null),
        departmentsService.getAll().catch(() => []),
      ]);

      setAnalytics(analyticsData);
      setDepartments(departmentsData);
    } catch (err: any) {
      const errorMsg = err.response?.data?.message || 'Failed to load dashboard data';
      setError(errorMsg);
      enqueueSnackbar(errorMsg, { variant: 'error' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Box>
      {/* Header */}
      <Box sx={{ mb: 4 }}>
        <Typography variant={isMobile ? 'h5' : 'h4'} sx={{ fontWeight: 700, mb: 1 }}>
          Dashboard Overview
        </Typography>
        <Typography variant="body2" color="text.secondary">
          Welcome back! Here's what's happening with your KPIs today.
        </Typography>
      </Box>

      {error && (
        <Alert severity="error" sx={{ mb: 3 }} onClose={() => setError(null)}>
          {error}
        </Alert>
      )}

      {/* Stats Grid */}
      <Grid container spacing={isMobile ? 2 : 3} sx={{ mb: 4 }}>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            title="Active Users"
            value={analytics?.active_users || 0}
            subtitle={`${analytics?.total_users || 0} total users`}
            icon={<People />}
            color="#1565C0"
            trend={12}
            loading={loading}
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            title="Departments"
            value={departments.length}
            subtitle={`${departments.filter(d => d.is_active).length} active`}
            icon={<Business />}
            color="#2E7D32"
            loading={loading}
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            title="Active KPIs"
            value={analytics?.active_kpis || 0}
            subtitle={`${analytics?.total_kpis || 0} total KPIs`}
            icon={<Assessment />}
            color="#ED6C02"
            trend={8}
            loading={loading}
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            title="Pending Approvals"
            value={analytics?.pending_approvals || 0}
            subtitle="Awaiting review"
            icon={<PendingActions />}
            color="#D32F2F"
            loading={loading}
          />
        </Grid>
      </Grid>

      <Grid container spacing={isMobile ? 2 : 3}>
        {/* Departments List */}
        <Grid item xs={12} md={8}>
          <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider' }}>
            <CardContent>
              <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 3 }}>
                <Typography variant="h6" sx={{ fontWeight: 600 }}>
                  Departments
                </Typography>
                <Chip
                  label={`${departments.length} total`}
                  size="small"
                  color="primary"
                  variant="outlined"
                />
              </Box>

              {loading ? (
                <Box sx={{ display: 'flex', justifyContent: 'center', py: 4 }}>
                  <CircularProgress />
                </Box>
              ) : departments.length === 0 ? (
                <Box sx={{ textAlign: 'center', py: 4 }}>
                  <Business sx={{ fontSize: 60, color: 'text.secondary', mb: 2 }} />
                  <Typography variant="body2" color="text.secondary">
                    No departments configured yet
                  </Typography>
                </Box>
              ) : (
                <Grid container spacing={2}>
                  {departments.slice(0, 6).map((dept) => (
                    <Grid item xs={12} sm={6} key={dept.id}>
                      <Paper
                        elevation={0}
                        sx={{
                          p: 2,
                          border: '1px solid',
                          borderColor: 'divider',
                          borderLeft: `4px solid ${dept.color_code}`,
                          transition: 'all 0.2s',
                          '&:hover': {
                            borderColor: dept.color_code,
                            transform: 'translateX(4px)',
                            boxShadow: `0 4px 12px ${dept.color_code}40`,
                          },
                        }}
                      >
                        <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5, flex: 1 }}>
                            <Avatar
                              sx={{
                                bgcolor: `${dept.color_code}20`,
                                color: dept.color_code,
                                width: 40,
                                height: 40,
                              }}
                            >
                              <Business />
                            </Avatar>
                            <Box sx={{ flex: 1, minWidth: 0 }}>
                              <Typography
                                variant="subtitle2"
                                sx={{
                                  fontWeight: 600,
                                  overflow: 'hidden',
                                  textOverflow: 'ellipsis',
                                  whiteSpace: 'nowrap',
                                }}
                              >
                                {dept.name}
                              </Typography>
                              <Typography
                                variant="caption"
                                color="text.secondary"
                                sx={{
                                  overflow: 'hidden',
                                  textOverflow: 'ellipsis',
                                  whiteSpace: 'nowrap',
                                  display: 'block',
                                }}
                              >
                                {dept.description || dept.slug}
                              </Typography>
                            </Box>
                          </Box>
                          <Chip
                            label={dept.is_active ? 'Active' : 'Inactive'}
                            size="small"
                            color={dept.is_active ? 'success' : 'default'}
                            sx={{ ml: 1 }}
                          />
                        </Box>
                      </Paper>
                    </Grid>
                  ))}
                </Grid>
              )}
            </CardContent>
          </Card>
        </Grid>

        {/* Quick Stats */}
        <Grid item xs={12} md={4}>
          <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider', mb: isMobile ? 2 : 3 }}>
            <CardContent>
              <Typography variant="h6" sx={{ fontWeight: 600, mb: 3 }}>
                Data Completion
              </Typography>

              <Box sx={{ mb: 3 }}>
                <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                  <Typography variant="body2" color="text.secondary">
                    Overall Progress
                  </Typography>
                  <Typography variant="body2" sx={{ fontWeight: 600 }}>
                    {analytics?.completion_rate || 0}%
                  </Typography>
                </Box>
                <LinearProgress
                  variant="determinate"
                  value={analytics?.completion_rate || 0}
                  sx={{
                    height: 8,
                    borderRadius: 4,
                    bgcolor: '#E0E0E0',
                    '& .MuiLinearProgress-bar': {
                      borderRadius: 4,
                      background: 'linear-gradient(90deg, #1565C0 0%, #42A5F5 100%)',
                    },
                  }}
                />
              </Box>

              <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                <CheckCircle sx={{ color: '#2E7D32', fontSize: 20, mr: 1 }} />
                <Typography variant="body2" sx={{ flex: 1 }}>
                  Data Entries
                </Typography>
                <Typography variant="body2" sx={{ fontWeight: 600 }}>
                  {analytics?.total_data_entries || 0}
                </Typography>
              </Box>

              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <Schedule sx={{ color: '#ED6C02', fontSize: 20, mr: 1 }} />
                <Typography variant="body2" sx={{ flex: 1 }}>
                  Pending Approvals
                </Typography>
                <Typography variant="body2" sx={{ fontWeight: 600 }}>
                  {analytics?.pending_approvals || 0}
                </Typography>
              </Box>
            </CardContent>
          </Card>

          <Card
            elevation={0}
            sx={{
              border: '1px solid',
              borderColor: 'divider',
              background: 'linear-gradient(135deg, #1565C015 0%, #42A5F505 100%)',
            }}
          >
            <CardContent>
              <Typography variant="h6" sx={{ fontWeight: 600, mb: 2 }}>
                Quick Actions
              </Typography>

              <Box sx={{ display: 'flex', flexDirection: 'column', gap: 1 }}>
                <Paper
                  elevation={0}
                  sx={{
                    p: 1.5,
                    cursor: 'pointer',
                    border: '1px solid',
                    borderColor: 'divider',
                    transition: 'all 0.2s',
                    '&:hover': {
                      borderColor: '#1565C0',
                      bgcolor: '#1565C010',
                    },
                  }}
                >
                  <Typography variant="body2" sx={{ fontWeight: 500 }}>
                    📊 View Analytics
                  </Typography>
                </Paper>

                <Paper
                  elevation={0}
                  sx={{
                    p: 1.5,
                    cursor: 'pointer',
                    border: '1px solid',
                    borderColor: 'divider',
                    transition: 'all 0.2s',
                    '&:hover': {
                      borderColor: '#2E7D32',
                      bgcolor: '#2E7D3210',
                    },
                  }}
                >
                  <Typography variant="body2" sx={{ fontWeight: 500 }}>
                    ➕ Add New KPI
                  </Typography>
                </Paper>

                <Paper
                  elevation={0}
                  sx={{
                    p: 1.5,
                    cursor: 'pointer',
                    border: '1px solid',
                    borderColor: 'divider',
                    transition: 'all 0.2s',
                    '&:hover': {
                      borderColor: '#ED6C02',
                      bgcolor: '#ED6C0210',
                    },
                  }}
                >
                  <Typography variant="body2" sx={{ fontWeight: 500 }}>
                    📈 Generate Report
                  </Typography>
                </Paper>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>
    </Box>
  );
}
