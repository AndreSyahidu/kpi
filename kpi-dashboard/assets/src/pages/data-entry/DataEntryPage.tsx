import { useState, useEffect } from 'react';
import {
  Box, Typography, Card, CardContent, Grid, TextField, Button, Table, TableBody,
  TableCell, TableContainer, TableHead, TableRow, Paper, Chip, IconButton,
  Dialog, DialogTitle, DialogContent, DialogActions, Alert, CircularProgress,
  MenuItem, useTheme, useMediaQuery, Fab, Avatar, Tooltip, InputAdornment,
} from '@mui/material';
import {
  Add as AddIcon, Edit as EditIcon, Delete as DeleteIcon, Assessment as AssessmentIcon,
  CloudUpload as UploadIcon, Download as DownloadIcon, Search as SearchIcon,
  TrendingUp as TrendingUpIcon, CalendarToday as CalendarIcon,
} from '@mui/icons-material';
import { useSnackbar } from 'notistack';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { DatePicker } from '@mui/x-date-pickers/DatePicker';
import dayjs, { Dayjs } from 'dayjs';
import dataService, { KPIData, CreateKPIDataInput } from '@/services/data.service';
import kpisService, { KPI } from '@/services/kpis.service';
import { useAuthStore } from '@/store/auth.store';

const statusColors = {
  draft: '#757575',
  submitted: '#1976D2',
  approved: '#2E7D32',
  rejected: '#D32F2F',
};

export default function DataEntryPage() {
  const { enqueueSnackbar } = useSnackbar();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  const isTablet = useMediaQuery(theme.breakpoints.down('md'));
  const user = useAuthStore((state) => state.user);

  const [kpis, setKpis] = useState<KPI[]>([]);
  const [dataEntries, setDataEntries] = useState<KPIData[]>([]);
  const [filteredEntries, setFilteredEntries] = useState<KPIData[]>([]);
  const [loading, setLoading] = useState(true);
  const [openDialog, setOpenDialog] = useState(false);
  const [editingEntry, setEditingEntry] = useState<KPIData | null>(null);

  // Filters
  const [selectedKpi, setSelectedKpi] = useState<number | null>(null);
  const [startDate, setStartDate] = useState<Dayjs | null>(null);
  const [endDate, setEndDate] = useState<Dayjs | null>(null);
  const [searchQuery, setSearchQuery] = useState('');

  // Form data
  const [formData, setFormData] = useState<CreateKPIDataInput>({
    kpi_id: 0,
    period_start: dayjs().startOf('month').format('YYYY-MM-DD'),
    period_end: dayjs().endOf('month').format('YYYY-MM-DD'),
    value: 0,
    notes: '',
  });

  useEffect(() => {
    loadData();
  }, []);

  useEffect(() => {
    filterEntries();
  }, [searchQuery, selectedKpi, startDate, endDate, dataEntries]);

  const loadData = async () => {
    try {
      setLoading(true);

      const [kpisData, entriesData] = await Promise.all([
        kpisService.getAll(),
        dataService.getAll(),
      ]);

      setKpis(kpisData.filter((kpi) => kpi.is_active));
      setDataEntries(entriesData);
    } catch (err: any) {
      const errorMsg = err.response?.data?.message || 'Failed to load data';
      enqueueSnackbar(errorMsg, { variant: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const filterEntries = () => {
    let filtered = [...dataEntries];

    // Filter by KPI
    if (selectedKpi) {
      filtered = filtered.filter((entry) => entry.kpi_id === selectedKpi);
    }

    // Filter by date range
    if (startDate) {
      filtered = filtered.filter((entry) => dayjs(entry.period_start).isAfter(startDate) || dayjs(entry.period_start).isSame(startDate));
    }
    if (endDate) {
      filtered = filtered.filter((entry) => dayjs(entry.period_end).isBefore(endDate) || dayjs(entry.period_end).isSame(endDate));
    }

    // Filter by search query
    if (searchQuery.trim()) {
      const query = searchQuery.toLowerCase();
      filtered = filtered.filter(
        (entry) =>
          entry.notes?.toLowerCase().includes(query) ||
          entry.kpi_name?.toLowerCase().includes(query)
      );
    }

    setFilteredEntries(filtered);
  };

  const handleOpenDialog = (entry?: KPIData) => {
    if (entry) {
      setEditingEntry(entry);
      setFormData({
        kpi_id: entry.kpi_id,
        period_start: entry.period_start,
        period_end: entry.period_end,
        value: entry.value,
        notes: entry.notes || '',
      });
    } else {
      setEditingEntry(null);
      setFormData({
        kpi_id: kpis.length > 0 ? kpis[0].id : 0,
        period_start: dayjs().startOf('month').format('YYYY-MM-DD'),
        period_end: dayjs().endOf('month').format('YYYY-MM-DD'),
        value: 0,
        notes: '',
      });
    }
    setOpenDialog(true);
  };

  const handleSubmit = async () => {
    if (!formData.kpi_id) {
      enqueueSnackbar('Please select a KPI', { variant: 'warning' });
      return;
    }

    try {
      if (editingEntry) {
        await dataService.update(editingEntry.id, formData);
        enqueueSnackbar('Data entry updated successfully', { variant: 'success' });
      } else {
        await dataService.create(formData);
        enqueueSnackbar('Data entry created successfully', { variant: 'success' });
      }
      setOpenDialog(false);
      loadData();
    } catch (err: any) {
      enqueueSnackbar(err.response?.data?.message || 'Failed to save data entry', { variant: 'error' });
    }
  };

  const handleDelete = async (id: number, kpiName: string) => {
    if (!confirm(`Delete data entry for "${kpiName}"?`)) return;

    try {
      await dataService.delete(id);
      enqueueSnackbar('Data entry deleted', { variant: 'success' });
      loadData();
    } catch (err: any) {
      enqueueSnackbar(err.response?.data?.message || 'Failed to delete', { variant: 'error' });
    }
  };

  const handleSubmitForApproval = async (id: number) => {
    try {
      await dataService.submitForApproval(id);
      enqueueSnackbar('Data submitted for approval', { variant: 'success' });
      loadData();
    } catch (err: any) {
      enqueueSnackbar(err.response?.data?.message || 'Failed to submit', { variant: 'error' });
    }
  };

  const getKPIName = (kpiId: number): string => {
    const kpi = kpis.find((k) => k.id === kpiId);
    return kpi?.name || 'Unknown KPI';
  };

  const getKPIUnit = (kpiId: number): string => {
    const kpi = kpis.find((k) => k.id === kpiId);
    return kpi?.unit || '';
  };

  if (loading) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: 400 }}>
        <CircularProgress size={isMobile ? 40 : 60} />
      </Box>
    );
  }

  return (
    <Box sx={{ pb: isMobile ? 10 : 0 }}>
      <Box sx={{ mb: 3 }}>
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', mb: 2 }}>
          <Box>
            <Typography variant={isMobile ? 'h5' : 'h4'} sx={{ fontWeight: 700, mb: 0.5 }}>
              <AssessmentIcon sx={{ mr: 1, verticalAlign: 'middle', fontSize: 'inherit' }} />
              Data Entry
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Enter and manage KPI data values
            </Typography>
          </Box>
          {!isMobile && (
            <Button
              variant="contained"
              startIcon={<AddIcon />}
              onClick={() => handleOpenDialog()}
              size={isTablet ? 'medium' : 'large'}
            >
              Add Entry
            </Button>
          )}
        </Box>

        {/* Filters */}
        <Card elevation={0} sx={{ mb: 3, border: '1px solid', borderColor: 'divider' }}>
          <CardContent sx={{ p: isMobile ? 2 : 3 }}>
            <Grid container spacing={2}>
              <Grid item xs={12} sm={6} md={3}>
                <TextField
                  fullWidth
                  select
                  label="Filter by KPI"
                  value={selectedKpi || ''}
                  onChange={(e) => setSelectedKpi(e.target.value ? Number(e.target.value) : null)}
                  size={isMobile ? 'small' : 'medium'}
                >
                  <MenuItem value="">All KPIs</MenuItem>
                  {kpis.map((kpi) => (
                    <MenuItem key={kpi.id} value={kpi.id}>
                      {kpi.name}
                    </MenuItem>
                  ))}
                </TextField>
              </Grid>
              <Grid item xs={12} sm={6} md={3}>
                <LocalizationProvider dateAdapter={AdapterDayjs}>
                  <DatePicker
                    label="Start Date"
                    value={startDate}
                    onChange={(newValue) => setStartDate(newValue)}
                    slotProps={{
                      textField: {
                        fullWidth: true,
                        size: isMobile ? 'small' : 'medium',
                      },
                    }}
                  />
                </LocalizationProvider>
              </Grid>
              <Grid item xs={12} sm={6} md={3}>
                <LocalizationProvider dateAdapter={AdapterDayjs}>
                  <DatePicker
                    label="End Date"
                    value={endDate}
                    onChange={(newValue) => setEndDate(newValue)}
                    slotProps={{
                      textField: {
                        fullWidth: true,
                        size: isMobile ? 'small' : 'medium',
                      },
                    }}
                  />
                </LocalizationProvider>
              </Grid>
              <Grid item xs={12} sm={6} md={3}>
                <TextField
                  fullWidth
                  placeholder="Search notes..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  size={isMobile ? 'small' : 'medium'}
                  InputProps={{
                    startAdornment: (
                      <InputAdornment position="start">
                        <SearchIcon />
                      </InputAdornment>
                    ),
                  }}
                />
              </Grid>
            </Grid>
          </CardContent>
        </Card>
      </Box>

      <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider' }}>
        <CardContent sx={{ p: isMobile ? 2 : 3 }}>
          {filteredEntries.length === 0 ? (
            <Box sx={{ textAlign: 'center', py: isMobile ? 4 : 8 }}>
              <AssessmentIcon sx={{ fontSize: isMobile ? 60 : 80, color: 'text.secondary', mb: 2 }} />
              <Typography variant={isMobile ? 'h6' : 'h5'} color="text.secondary" sx={{ mb: 1 }}>
                {searchQuery || selectedKpi || startDate || endDate ? 'No data entries found' : 'No data entries yet'}
              </Typography>
              <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                {searchQuery || selectedKpi || startDate || endDate
                  ? 'Try adjusting your filters'
                  : 'Start by adding your first KPI data entry'}
              </Typography>
              {!(searchQuery || selectedKpi || startDate || endDate) && (
                <Button
                  variant="contained"
                  startIcon={<AddIcon />}
                  onClick={() => handleOpenDialog()}
                  size={isMobile ? 'medium' : 'large'}
                >
                  Add Entry
                </Button>
              )}
            </Box>
          ) : isMobile || isTablet ? (
            <Grid container spacing={2}>
              {filteredEntries.map((entry) => (
                <Grid item xs={12} sm={6} key={entry.id}>
                  <Paper
                    elevation={0}
                    sx={{
                      p: 2,
                      border: '1px solid',
                      borderColor: 'divider',
                      borderLeft: `4px solid ${statusColors[entry.status]}`,
                      transition: 'all 0.2s',
                      '&:hover': {
                        borderColor: statusColors[entry.status],
                        transform: 'translateY(-2px)',
                        boxShadow: `0 4px 12px ${statusColors[entry.status]}40`,
                      },
                    }}
                  >
                    <Box sx={{ display: 'flex', alignItems: 'flex-start', gap: 2 }}>
                      <Avatar
                        sx={{
                          bgcolor: `${statusColors[entry.status]}20`,
                          color: statusColors[entry.status],
                          width: 48,
                          height: 48,
                        }}
                      >
                        <TrendingUpIcon />
                      </Avatar>
                      <Box sx={{ flex: 1, minWidth: 0 }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 0.5 }}>
                          <Typography variant="subtitle1" sx={{ fontWeight: 600 }}>
                            {getKPIName(entry.kpi_id)}
                          </Typography>
                        </Box>
                        <Typography variant="h6" sx={{ color: statusColors[entry.status], fontWeight: 700, mb: 1 }}>
                          {entry.value} {getKPIUnit(entry.kpi_id)}
                        </Typography>
                        <Box sx={{ display: 'flex', gap: 1, mb: 1, flexWrap: 'wrap' }}>
                          <Chip
                            label={entry.status}
                            size="small"
                            sx={{
                              bgcolor: `${statusColors[entry.status]}20`,
                              color: statusColors[entry.status],
                              textTransform: 'capitalize',
                            }}
                          />
                          <Chip
                            icon={<CalendarIcon fontSize="small" />}
                            label={`${dayjs(entry.period_start).format('MMM DD')} - ${dayjs(entry.period_end).format('MMM DD')}`}
                            size="small"
                            variant="outlined"
                          />
                        </Box>
                        {entry.notes && (
                          <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                            {entry.notes}
                          </Typography>
                        )}
                        <Box sx={{ display: 'flex', gap: 1, mt: 2 }}>
                          {entry.status === 'draft' && (
                            <>
                              <Button
                                size="small"
                                variant="outlined"
                                startIcon={<EditIcon fontSize="small" />}
                                onClick={() => handleOpenDialog(entry)}
                              >
                                Edit
                              </Button>
                              <Button
                                size="small"
                                variant="contained"
                                onClick={() => handleSubmitForApproval(entry.id)}
                              >
                                Submit
                              </Button>
                            </>
                          )}
                          {entry.status === 'draft' && (
                            <IconButton
                              size="small"
                              color="error"
                              onClick={() => handleDelete(entry.id, getKPIName(entry.kpi_id))}
                            >
                              <DeleteIcon fontSize="small" />
                            </IconButton>
                          )}
                        </Box>
                      </Box>
                    </Box>
                  </Paper>
                </Grid>
              ))}
            </Grid>
          ) : (
            <TableContainer component={Paper} elevation={0}>
              <Table>
                <TableHead>
                  <TableRow>
                    <TableCell sx={{ fontWeight: 600 }}>KPI</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Value</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Period</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Status</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Notes</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Submitted By</TableCell>
                    <TableCell align="right" sx={{ fontWeight: 600 }}>Actions</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {filteredEntries.map((entry) => (
                    <TableRow key={entry.id} hover>
                      <TableCell>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                          <Avatar
                            sx={{
                              bgcolor: `${statusColors[entry.status]}20`,
                              color: statusColors[entry.status],
                              width: 40,
                              height: 40,
                            }}
                          >
                            <TrendingUpIcon fontSize="small" />
                          </Avatar>
                          <Typography variant="body2" sx={{ fontWeight: 600 }}>
                            {getKPIName(entry.kpi_id)}
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell>
                        <Typography variant="body2" sx={{ fontWeight: 700, color: statusColors[entry.status] }}>
                          {entry.value} {getKPIUnit(entry.kpi_id)}
                        </Typography>
                      </TableCell>
                      <TableCell>
                        <Typography variant="body2">
                          {dayjs(entry.period_start).format('MMM DD, YYYY')}
                        </Typography>
                        <Typography variant="caption" color="text.secondary">
                          to {dayjs(entry.period_end).format('MMM DD, YYYY')}
                        </Typography>
                      </TableCell>
                      <TableCell>
                        <Chip
                          label={entry.status}
                          size="small"
                          sx={{
                            bgcolor: `${statusColors[entry.status]}20`,
                            color: statusColors[entry.status],
                            textTransform: 'capitalize',
                          }}
                        />
                      </TableCell>
                      <TableCell>
                        <Typography variant="body2" sx={{ maxWidth: 200, overflow: 'hidden', textOverflow: 'ellipsis' }}>
                          {entry.notes || '-'}
                        </Typography>
                      </TableCell>
                      <TableCell>
                        <Typography variant="body2">{entry.submitted_by_name || '-'}</Typography>
                      </TableCell>
                      <TableCell align="right">
                        {entry.status === 'draft' && (
                          <>
                            <Tooltip title="Edit">
                              <IconButton size="small" color="primary" onClick={() => handleOpenDialog(entry)}>
                                <EditIcon fontSize="small" />
                              </IconButton>
                            </Tooltip>
                            <Tooltip title="Submit for Approval">
                              <Button
                                size="small"
                                variant="outlined"
                                sx={{ mx: 1 }}
                                onClick={() => handleSubmitForApproval(entry.id)}
                              >
                                Submit
                              </Button>
                            </Tooltip>
                            <Tooltip title="Delete">
                              <IconButton
                                size="small"
                                color="error"
                                onClick={() => handleDelete(entry.id, getKPIName(entry.kpi_id))}
                              >
                                <DeleteIcon fontSize="small" />
                              </IconButton>
                            </Tooltip>
                          </>
                        )}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </TableContainer>
          )}

          {filteredEntries.length > 0 && (
            <Box sx={{ mt: 2 }}>
              <Typography variant="body2" color="text.secondary">
                Showing {filteredEntries.length} of {dataEntries.length} entries
              </Typography>
            </Box>
          )}
        </CardContent>
      </Card>

      {isMobile && (
        <Fab
          color="primary"
          aria-label="add"
          onClick={() => handleOpenDialog()}
          sx={{ position: 'fixed', bottom: 16, right: 16, boxShadow: 4 }}
        >
          <AddIcon />
        </Fab>
      )}

      {/* Add/Edit Dialog */}
      <Dialog open={openDialog} onClose={() => setOpenDialog(false)} maxWidth="md" fullWidth fullScreen={isMobile}>
        <DialogTitle sx={{ fontWeight: 600 }}>
          {editingEntry ? 'Edit Data Entry' : 'Add New Data Entry'}
        </DialogTitle>
        <DialogContent>
          <Grid container spacing={2} sx={{ mt: 0.5 }}>
            <Grid item xs={12}>
              <TextField
                fullWidth
                select
                label="KPI"
                value={formData.kpi_id}
                onChange={(e) => setFormData({ ...formData, kpi_id: Number(e.target.value) })}
                required
              >
                {kpis.map((kpi) => (
                  <MenuItem key={kpi.id} value={kpi.id}>
                    {kpi.name} ({kpi.unit || 'no unit'})
                  </MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Value"
                type="number"
                value={formData.value}
                onChange={(e) => setFormData({ ...formData, value: Number(e.target.value) })}
                required
                InputProps={{
                  endAdornment: formData.kpi_id ? (
                    <InputAdornment position="end">{getKPIUnit(formData.kpi_id)}</InputAdornment>
                  ) : null,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={6}></Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Period Start"
                type="date"
                value={formData.period_start}
                onChange={(e) => setFormData({ ...formData, period_start: e.target.value })}
                InputLabelProps={{ shrink: true }}
                required
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Period End"
                type="date"
                value={formData.period_end}
                onChange={(e) => setFormData({ ...formData, period_end: e.target.value })}
                InputLabelProps={{ shrink: true }}
                required
              />
            </Grid>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Notes"
                value={formData.notes || ''}
                onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                multiline
                rows={3}
                placeholder="Add any relevant notes or comments..."
              />
            </Grid>
          </Grid>
        </DialogContent>
        <DialogActions sx={{ px: 3, pb: 3 }}>
          <Button onClick={() => setOpenDialog(false)} color="inherit">
            Cancel
          </Button>
          <Button onClick={handleSubmit} variant="contained" disabled={!formData.kpi_id || !formData.value}>
            {editingEntry ? 'Update' : 'Create'}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
