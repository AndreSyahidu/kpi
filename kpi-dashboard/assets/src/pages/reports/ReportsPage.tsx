import { useState, useEffect } from 'react';
import {
  Box, Typography, Card, CardContent, Grid, TextField, Button,
  CircularProgress, MenuItem, useTheme, useMediaQuery, Avatar, Alert,
  List, ListItem, ListItemAvatar, ListItemText, ListItemSecondaryAction,
  IconButton, Divider,
} from '@mui/material';
import {
  Assessment as ReportIcon, Business as DepartmentIcon, TrendingUp as KPIIcon,
  Download as DownloadIcon, PictureAsPdf as PdfIcon, TableChart as ExcelIcon,
  CalendarToday as CalendarIcon, PlayArrow as GenerateIcon,
} from '@mui/icons-material';
import { useSnackbar } from 'notistack';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { DatePicker } from '@mui/x-date-pickers/DatePicker';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import dayjs, { Dayjs } from 'dayjs';
import reportsService from '@/services/reports.service';
import kpisService, { KPI } from '@/services/kpis.service';
import departmentsService, { Department } from '@/services/departments.service';

export default function ReportsPage() {
  const { enqueueSnackbar } = useSnackbar();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));

  const [loading, setLoading] = useState(true);
  const [generating, setGenerating] = useState(false);
  const [kpis, setKpis] = useState<KPI[]>([]);
  const [departments, setDepartments] = useState<Department[]>([]);

  // Form state
  const [reportType, setReportType] = useState<'department' | 'executive' | 'kpi'>('executive');
  const [selectedDepartment, setSelectedDepartment] = useState<number | null>(null);
  const [selectedKPI, setSelectedKPI] = useState<number | null>(null);
  const [periodStart, setPeriodStart] = useState<Dayjs | null>(dayjs().subtract(1, 'month').startOf('month'));
  const [periodEnd, setPeriodEnd] = useState<Dayjs | null>(dayjs().subtract(1, 'month').endOf('month'));
  const [format, setFormat] = useState<'json' | 'pdf' | 'excel'>('pdf');

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);
      const [kpisData, deptsData] = await Promise.all([
        kpisService.getAll(),
        departmentsService.getAll(),
      ]);
      setKpis(kpisData);
      setDepartments(deptsData);
    } catch (err: any) {
      const errorMsg = err.response?.data?.message || 'Failed to load data';
      enqueueSnackbar(errorMsg, { variant: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const handleGenerateReport = async () => {
    try {
      setGenerating(true);

      const params = {
        period_start: periodStart?.format('YYYY-MM-DD'),
        period_end: periodEnd?.format('YYYY-MM-DD'),
        format,
      };

      let reportData;

      if (reportType === 'department') {
        if (!selectedDepartment) {
          enqueueSnackbar('Please select a department', { variant: 'warning' });
          setGenerating(false);
          return;
        }
        reportData = await reportsService.generateDepartmentReport(selectedDepartment, params);
      } else if (reportType === 'kpi') {
        if (!selectedKPI) {
          enqueueSnackbar('Please select a KPI', { variant: 'warning' });
          setGenerating(false);
          return;
        }
        reportData = await reportsService.generateKPIReport(selectedKPI, params);
      } else {
        reportData = await reportsService.generateExecutiveReport(params);
      }

      enqueueSnackbar('Report generated successfully!', { variant: 'success' });

      // If the format is PDF or Excel, trigger download
      if (format !== 'json' && reportData.download_url) {
        window.open(reportData.download_url, '_blank');
      }
    } catch (err: any) {
      enqueueSnackbar(err.response?.data?.message || 'Failed to generate report', { variant: 'error' });
    } finally {
      setGenerating(false);
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
          <ReportIcon sx={{ mr: 1, verticalAlign: 'middle', fontSize: 'inherit' }} />
          Reports
        </Typography>
        <Typography variant="body2" color="text.secondary">
          Generate and export KPI reports
        </Typography>
      </Box>

      <Grid container spacing={3}>
        {/* Report Generator */}
        <Grid item xs={12} md={7}>
          <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider' }}>
            <CardContent sx={{ p: isMobile ? 2 : 3 }}>
              <Typography variant="h6" sx={{ fontWeight: 600, mb: 3 }}>
                Generate Report
              </Typography>

              <Grid container spacing={2}>
                <Grid item xs={12}>
                  <TextField
                    fullWidth
                    select
                    label="Report Type"
                    value={reportType}
                    onChange={(e) => setReportType(e.target.value as any)}
                  >
                    <MenuItem value="executive">
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                        <ReportIcon fontSize="small" />
                        Executive Summary
                      </Box>
                    </MenuItem>
                    <MenuItem value="department">
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                        <DepartmentIcon fontSize="small" />
                        Department Report
                      </Box>
                    </MenuItem>
                    <MenuItem value="kpi">
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                        <KPIIcon fontSize="small" />
                        KPI Detail Report
                      </Box>
                    </MenuItem>
                  </TextField>
                </Grid>

                {reportType === 'department' && (
                  <Grid item xs={12}>
                    <TextField
                      fullWidth
                      select
                      label="Select Department"
                      value={selectedDepartment || ''}
                      onChange={(e) => setSelectedDepartment(Number(e.target.value))}
                      required
                    >
                      {departments.map((dept) => (
                        <MenuItem key={dept.id} value={dept.id}>
                          {dept.name}
                        </MenuItem>
                      ))}
                    </TextField>
                  </Grid>
                )}

                {reportType === 'kpi' && (
                  <Grid item xs={12}>
                    <TextField
                      fullWidth
                      select
                      label="Select KPI"
                      value={selectedKPI || ''}
                      onChange={(e) => setSelectedKPI(Number(e.target.value))}
                      required
                    >
                      {kpis.map((kpi) => (
                        <MenuItem key={kpi.id} value={kpi.id}>
                          {kpi.name}
                        </MenuItem>
                      ))}
                    </TextField>
                  </Grid>
                )}

                <Grid item xs={12} sm={6}>
                  <LocalizationProvider dateAdapter={AdapterDayjs}>
                    <DatePicker
                      label="Period Start"
                      value={periodStart}
                      onChange={(newValue) => setPeriodStart(newValue)}
                      slotProps={{ textField: { fullWidth: true } }}
                    />
                  </LocalizationProvider>
                </Grid>

                <Grid item xs={12} sm={6}>
                  <LocalizationProvider dateAdapter={AdapterDayjs}>
                    <DatePicker
                      label="Period End"
                      value={periodEnd}
                      onChange={(newValue) => setPeriodEnd(newValue)}
                      slotProps={{ textField: { fullWidth: true } }}
                    />
                  </LocalizationProvider>
                </Grid>

                <Grid item xs={12}>
                  <TextField
                    fullWidth
                    select
                    label="Export Format"
                    value={format}
                    onChange={(e) => setFormat(e.target.value as any)}
                  >
                    <MenuItem value="pdf">
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                        <PdfIcon fontSize="small" />
                        PDF Document
                      </Box>
                    </MenuItem>
                    <MenuItem value="excel">
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                        <ExcelIcon fontSize="small" />
                        Excel Spreadsheet
                      </Box>
                    </MenuItem>
                    <MenuItem value="json">
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                        <ReportIcon fontSize="small" />
                        JSON Data
                      </Box>
                    </MenuItem>
                  </TextField>
                </Grid>

                <Grid item xs={12}>
                  <Button
                    fullWidth
                    variant="contained"
                    size="large"
                    startIcon={generating ? <CircularProgress size={20} color="inherit" /> : <GenerateIcon />}
                    onClick={handleGenerateReport}
                    disabled={generating}
                  >
                    {generating ? 'Generating Report...' : 'Generate Report'}
                  </Button>
                </Grid>
              </Grid>

              <Alert severity="info" sx={{ mt: 3 }}>
                <Typography variant="body2" sx={{ fontWeight: 600, mb: 0.5 }}>
                  Report Information
                </Typography>
                <Typography variant="body2">
                  {reportType === 'executive' && 'Executive summary includes overall KPI performance across all departments.'}
                  {reportType === 'department' && 'Department report shows all KPIs for the selected department.'}
                  {reportType === 'kpi' && 'KPI detail report provides in-depth analysis for a specific KPI.'}
                </Typography>
              </Alert>
            </CardContent>
          </Card>
        </Grid>

        {/* Quick Reports */}
        <Grid item xs={12} md={5}>
          <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider' }}>
            <CardContent sx={{ p: isMobile ? 2 : 3 }}>
              <Typography variant="h6" sx={{ fontWeight: 600, mb: 3 }}>
                Quick Reports
              </Typography>

              <List>
                <ListItem
                  sx={{
                    borderRadius: 1,
                    border: '1px solid',
                    borderColor: 'divider',
                    mb: 1,
                    '&:hover': {
                      bgcolor: '#1976D210',
                      borderColor: '#1976D2',
                    },
                  }}
                >
                  <ListItemAvatar>
                    <Avatar sx={{ bgcolor: '#1976D220', color: '#1976D2' }}>
                      <CalendarIcon />
                    </Avatar>
                  </ListItemAvatar>
                  <ListItemText
                    primary="Last Month Summary"
                    secondary="Executive summary for the previous month"
                  />
                  <ListItemSecondaryAction>
                    <IconButton
                      edge="end"
                      onClick={() => {
                        setReportType('executive');
                        setPeriodStart(dayjs().subtract(1, 'month').startOf('month'));
                        setPeriodEnd(dayjs().subtract(1, 'month').endOf('month'));
                        setFormat('pdf');
                      }}
                    >
                      <DownloadIcon />
                    </IconButton>
                  </ListItemSecondaryAction>
                </ListItem>

                <ListItem
                  sx={{
                    borderRadius: 1,
                    border: '1px solid',
                    borderColor: 'divider',
                    mb: 1,
                    '&:hover': {
                      bgcolor: '#2E7D3210',
                      borderColor: '#2E7D32',
                    },
                  }}
                >
                  <ListItemAvatar>
                    <Avatar sx={{ bgcolor: '#2E7D3220', color: '#2E7D32' }}>
                      <CalendarIcon />
                    </Avatar>
                  </ListItemAvatar>
                  <ListItemText
                    primary="Last Quarter Report"
                    secondary="Quarterly executive summary"
                  />
                  <ListItemSecondaryAction>
                    <IconButton
                      edge="end"
                      onClick={() => {
                        setReportType('executive');
                        setPeriodStart(dayjs().subtract(3, 'month').startOf('month'));
                        setPeriodEnd(dayjs().subtract(1, 'month').endOf('month'));
                        setFormat('pdf');
                      }}
                    >
                      <DownloadIcon />
                    </IconButton>
                  </ListItemSecondaryAction>
                </ListItem>

                <ListItem
                  sx={{
                    borderRadius: 1,
                    border: '1px solid',
                    borderColor: 'divider',
                    mb: 1,
                    '&:hover': {
                      bgcolor: '#ED6C0210',
                      borderColor: '#ED6C02',
                    },
                  }}
                >
                  <ListItemAvatar>
                    <Avatar sx={{ bgcolor: '#ED6C0220', color: '#ED6C02' }}>
                      <CalendarIcon />
                    </Avatar>
                  </ListItemAvatar>
                  <ListItemText
                    primary="Year-to-Date Report"
                    secondary="YTD performance summary"
                  />
                  <ListItemSecondaryAction>
                    <IconButton
                      edge="end"
                      onClick={() => {
                        setReportType('executive');
                        setPeriodStart(dayjs().startOf('year'));
                        setPeriodEnd(dayjs());
                        setFormat('pdf');
                      }}
                    >
                      <DownloadIcon />
                    </IconButton>
                  </ListItemSecondaryAction>
                </ListItem>
              </List>

              <Divider sx={{ my: 2 }} />

              <Typography variant="body2" color="text.secondary">
                Click on the quick report templates to populate the form with predefined settings.
              </Typography>
            </CardContent>
          </Card>
        </Grid>
      </Grid>
    </Box>
  );
}
