import { Grid, Card, CardContent, Typography, Box } from '@mui/material';
import { People, Business, Assessment, PendingActions } from '@mui/icons-material';

const StatCard = ({ title, value, icon, color }: any) => (
  <Card>
    <CardContent>
      <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <Box>
          <Typography color="text.secondary" variant="body2">{title}</Typography>
          <Typography variant="h4" sx={{ mt: 1 }}>{value}</Typography>
        </Box>
        <Box sx={{ bgcolor: color, borderRadius: 2, p: 1.5, color: 'white' }}>
          {icon}
        </Box>
      </Box>
    </CardContent>
  </Card>
);

export default function OverviewPage() {
  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 3 }}>Dashboard Overview</Typography>
      
      <Grid container spacing={3}>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard title="Active Users" value="0" icon={<People />} color="#1565C0" />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard title="Departments" value="6" icon={<Business />} color="#2E7D32" />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard title="Active KPIs" value="0" icon={<Assessment />} color="#ED6C02" />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard title="Pending Approvals" value="0" icon={<PendingActions />} color="#D32F2F" />
        </Grid>
      </Grid>

      <Card sx={{ mt: 3 }}>
        <CardContent>
          <Typography variant="h6" sx={{ mb: 2 }}>Welcome to KPI Dashboard</Typography>
          <Typography color="text.secondary">
            This is your centralized KPI management system. Use the sidebar to navigate to different sections.
          </Typography>
        </CardContent>
      </Card>
    </Box>
  );
}
