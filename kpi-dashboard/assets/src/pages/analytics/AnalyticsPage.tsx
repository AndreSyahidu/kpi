import { useState, useEffect } from 'react';
import {
  Box, Typography, Card, CardContent, Grid, Paper, CircularProgress,
  LinearProgress, useTheme, useMediaQuery, Avatar, Chip,
} from '@mui/material';
import {
  TrendingUp, TrendingDown, Assessment, CheckCircle, Warning,
  People, Business, CalendarToday,
} from '@mui/icons-material';
import { useSnackbar } from 'notistack';
import analyticsService, { AnalyticsOverview } from '@/services/analytics.service';
import kpisService, { KPI } from '@/services/kpis.service';
import departmentsService, { Department } from '@/services/departments.service';

interface MetricCardProps {
  title: string;
  value: number | string;
  subtitle?: string;
  icon: React.ReactNode;
  color: string;
  trend?: number;
  progress?: number;
}

const MetricCard = ({ title, value, subtitle, icon, color, trend, progress }: MetricCardProps) => {
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));

  return (
    <Card
      elevation={0}
      sx={{
        height: '100%',
        background: `linear-gradient(135deg, ${color}15 0%, ${color}05 100%)`,
        border: `1px solid ${color}30`,
        transition: 'all 0.3s',
        '&:hover': {
          transform: 'translateY(-4px)',
          boxShadow: `0 8px 24px ${color}40`,
          borderColor: color,
        },
      }}
    >
      <CardContent>
        <Box sx={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between' }}>
          <Box sx={{ flex: 1 }}>
            <Typography color="text.secondary" variant="body2" sx={{ fontWeight: 500, mb: 1 }}>
              {title}
            </Typography>
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
                ) : trend < 0 ? (
                  <TrendingDown sx={{ fontSize: 16, color: '#D32F2F', mr: 0.5 }} />
                ) : null}
                <Typography
                  variant="caption"
                  sx={{
                    color: trend > 0 ? '#2E7D32' : trend < 0 ? '#D32F2F' : 'text.secondary',
                    fontWeight: 600,
                  }}
                >
                  {trend > 0 ? '+' : ''}{trend}% vs last period
                </Typography>
              </Box>
            )}
            {progress !== undefined && (
              <Box sx={{ mt: 1 }}>
                <LinearProgress
                  variant="determinate"
                  value={progress}
                  sx={{
                    height: 6,
                    borderRadius: 3,
                    bgcolor: '#E0E0E0',
                    '& .MuiLinearProgress-bar': {
                      borderRadius: 3,
                      bgcolor: color,
                    },
                  }}
                />
              </Box>
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

export default function AnalyticsPage() {
  const { enqueueSnackbar } = useSnackbar();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));

  const [loading, setLoading] = useState(true);
  const [analytics, setAnalytics] = useState<AnalyticsOverview | null>(null);
  const [kpis, setKpis] = useState<KPI[]>([]);
  const [departments, setDepartments] = useState<Department[]>([]);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);

      const [analyticsData, kpisData, depsData] = await Promise.all([
        analyticsService.getOverview().catch(() => null),
        kpisService.getAll().catch(() => []),
        departmentsService.getAll().catch(() => []),
      ]);

      setAnalytics(analyticsData);
      setKpis(kpisData);
      setDepartments(depsData);
    } catch (err: any) {
      enqueueSnackbar(err.response?.data?.message || 'Failed to load analytics', { variant: 'error' });
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: 400 }}>
        <CircularProgress size={isMobile ? 40 : 60} />
      </Box>
    );
  }

  const activeKpis = kpis.filter((k) => k.is_active).length;
  const activeDepts = departments.filter((d) => d.is_active).length;

  return (
    <Box sx={{ pb: isMobile ? 4 : 0 }}>
      <Box sx={{ mb: 3 }}>
        <Typography variant={isMobile ? 'h5' : 'h4'} sx={{ fontWeight: 700, mb: 0.5 }}>
          <Assessment sx={{ mr: 1, verticalAlign: 'middle', fontSize: 'inherit' }} />
          Analytics
        </Typography>
        <Typography variant="body2" color="text.secondary">
          Comprehensive KPI performance insights and trends
        </Typography>
      </Box>

      {/* Key Metrics */}
      <Grid container spacing={isMobile ? 2 : 3} sx={{ mb: 4 }}>
        <Grid item xs={12} sm={6} md={3}>
          <MetricCard
            title="Total Active KPIs"
            value={activeKpis}
            subtitle={`${kpis.length} total KPIs`}
            icon={<Assessment />}
            color="#1565C0"
            trend={8}
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <MetricCard
            title="Active Departments"
            value={activeDepts}
            subtitle={`${departments.length} total`}
            icon={<Business />}
            color="#2E7D32"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <MetricCard
            title="Data Completion"
            value={`${analytics?.completion_rate || 0}%`}
            subtitle="Overall progress"
            icon={<CheckCircle />}
            color="#ED6C02"
            progress={analytics?.completion_rate || 0}
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <MetricCard
            title="Pending Approvals"
            value={analytics?.pending_approvals || 0}
            subtitle="Awaiting review"
            icon={<Warning />}
            color="#D32F2F"
          />
        </Grid>
      </Grid>

      {/* KPI Performance Summary */}
      <Grid container spacing={isMobile ? 2 : 3} sx={{ mb: 4 }}>
        <Grid item xs={12} md={8}>
          <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider' }}>
            <CardContent sx={{ p: isMobile ? 2 : 3 }}>
              <Typography variant="h6" sx={{ fontWeight: 600, mb: 3 }}>
                KPI Performance by Type
              </Typography>

              <Grid container spacing={2}>
                {kpis.length === 0 ? (
                  <Grid item xs={12}>
                    <Box sx={{ textAlign: 'center', py: 4 }}>
                      <Assessment sx={{ fontSize: 60, color: 'text.secondary', mb: 2 }} />
                      <Typography color="text.secondary">
                        No KPIs configured yet
                      </Typography>
                    </Box>
                  </Grid>
                ) : (
                  <>
                    <Grid item xs={12} sm={4}>
                      <Paper
                        elevation={0}
                        sx={{
                          p: 2,
                          textAlign: 'center',
                          border: '1px solid',
                          borderColor: 'divider',
                          borderTop: '4px solid #2196F3',
                        }}
                      >
                        <Typography variant="h4" sx={{ fontWeight: 700, color: '#2196F3', mb: 0.5 }}>
                          {kpis.filter((k) => k.type === 'department').length}
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          Department KPIs
                        </Typography>
                      </Paper>
                    </Grid>
                    <Grid item xs={12} sm={4}>
                      <Paper
                        elevation={0}
                        sx={{
                          p: 2,
                          textAlign: 'center',
                          border: '1px solid',
                          borderColor: 'divider',
                          borderTop: '4px solid #FF9800',
                        }}
                      >
                        <Typography variant="h4" sx={{ fontWeight: 700, color: '#FF9800', mb: 0.5 }}>
                          {kpis.filter((k) => k.type === 'position').length}
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          Position KPIs
                        </Typography>
                      </Paper>
                    </Grid>
                    <Grid item xs={12} sm={4}>
                      <Paper
                        elevation={0}
                        sx={{
                          p: 2,
                          textAlign: 'center',
                          border: '1px solid',
                          borderColor: 'divider',
                          borderTop: '4px solid #9C27B0',
                        }}
                      >
                        <Typography variant="h4" sx={{ fontWeight: 700, color: '#9C27B0', mb: 0.5 }}>
                          {kpis.filter((k) => k.type === 'personal').length}
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          Personal KPIs
                        </Typography>
                      </Paper>
                    </Grid>
                  </>
                )}
              </Grid>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} md={4}>
          <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider', height: '100%' }}>
            <CardContent sx={{ p: isMobile ? 2 : 3 }}>
              <Typography variant="h6" sx={{ fontWeight: 600, mb: 3 }}>
                System Activity
              </Typography>

              <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                <People sx={{ color: '#1565C0', fontSize: 20, mr: 1 }} />
                <Box sx={{ flex: 1 }}>
                  <Typography variant="body2" sx={{ fontWeight: 600 }}>
                    Active Users
                  </Typography>
                  <Typography variant="caption" color="text.secondary">
                    {analytics?.active_users || 0} of {analytics?.total_users || 0} users
                  </Typography>
                </Box>
              </Box>

              <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                <CalendarToday sx={{ color: '#2E7D32', fontSize: 20, mr: 1 }} />
                <Box sx={{ flex: 1 }}>
                  <Typography variant="body2" sx={{ fontWeight: 600 }}>
                    Data Entries
                  </Typography>
                  <Typography variant="caption" color="text.secondary">
                    {analytics?.total_data_entries || 0} total entries
                  </Typography>
                </Box>
              </Box>

              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <CheckCircle sx={{ color: '#ED6C02', fontSize: 20, mr: 1 }} />
                <Box sx={{ flex: 1 }}>
                  <Typography variant="body2" sx={{ fontWeight: 600 }}>
                    Completion Rate
                  </Typography>
                  <Typography variant="caption" color="text.secondary">
                    {analytics?.completion_rate || 0}% on-time completion
                  </Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* Department Performance */}
      <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider' }}>
        <CardContent sx={{ p: isMobile ? 2 : 3 }}>
          <Typography variant="h6" sx={{ fontWeight: 600, mb: 3 }}>
            Department Overview
          </Typography>

          {departments.length === 0 ? (
            <Box sx={{ textAlign: 'center', py: 4 }}>
              <Business sx={{ fontSize: 60, color: 'text.secondary', mb: 2 }} />
              <Typography color="text.secondary">
                No departments configured yet
              </Typography>
            </Box>
          ) : (
            <Grid container spacing={2}>
              {departments.slice(0, 6).map((dept) => (
                <Grid item xs={12} sm={6} md={4} key={dept.id}>
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
                    <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 1 }}>
                      <Typography variant="subtitle2" sx={{ fontWeight: 600 }}>
                        {dept.name}
                      </Typography>
                      <Chip
                        label={dept.is_active ? 'Active' : 'Inactive'}
                        size="small"
                        color={dept.is_active ? 'success' : 'default'}
                        sx={{ height: 20 }}
                      />
                    </Box>
                    <Typography variant="caption" color="text.secondary">
                      {dept.description || dept.slug}
                    </Typography>
                  </Paper>
                </Grid>
              ))}
            </Grid>
          )}
        </CardContent>
      </Card>
    </Box>
  );
}
