import { useState, useEffect } from 'react';
import {
  Box, Typography, Card, CardContent, Table, TableBody, TableCell, TableContainer,
  TableHead, TableRow, Paper, Chip, IconButton, Button, CircularProgress, Alert,
  Dialog, DialogTitle, DialogContent, DialogActions, TextField, Grid, Avatar, Fab,
  useTheme, useMediaQuery, Tooltip, InputAdornment, MenuItem,
} from '@mui/material';
import {
  Edit as EditIcon, Delete as DeleteIcon, Add as AddIcon, Assessment as AssessmentIcon,
  Search as SearchIcon, TrendingUp as TrendingUpIcon,
} from '@mui/icons-material';
import { useSnackbar } from 'notistack';
import kpisService, { KPI, CreateKPIData } from '@/services/kpis.service';

const typeColors = { department: '#2196F3', position: '#FF9800', personal: '#9C27B0' };
const metricColors = { number: '#4CAF50', percentage: '#2196F3', ratio: '#FF9800', custom: '#9C27B0', boolean: '#607D8B' };

export default function KPIsPage() {
  const { enqueueSnackbar } = useSnackbar();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  const isTablet = useMediaQuery(theme.breakpoints.down('md'));

  const [kpis, setKpis] = useState<KPI[]>([]);
  const [filteredKpis, setFilteredKpis] = useState<KPI[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [openDialog, setOpenDialog] = useState(false);
  const [editingKpi, setEditingKpi] = useState<KPI | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [formData, setFormData] = useState<CreateKPIData>({
    name: '', type: 'department', metric_type: 'number', input_frequency: 'monthly',
  });

  useEffect(() => { loadKpis(); }, []);
  useEffect(() => { filterKpis(); }, [searchQuery, kpis]);

  const loadKpis = async () => {
    try {
      setLoading(true);
      setError(null);
      const data = await kpisService.getAll();
      setKpis(data);
    } catch (err: any) {
      const errorMsg = err.response?.data?.message || 'Failed to load KPIs';
      setError(errorMsg);
      enqueueSnackbar(errorMsg, { variant: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const filterKpis = () => {
    if (!searchQuery.trim()) { setFilteredKpis(kpis); return; }
    const query = searchQuery.toLowerCase();
    setFilteredKpis(kpis.filter(kpi =>
      kpi.name.toLowerCase().includes(query) ||
      (kpi.description && kpi.description.toLowerCase().includes(query))
    ));
  };

  const handleOpenDialog = (kpi?: KPI) => {
    if (kpi) {
      setEditingKpi(kpi);
      setFormData({ name: kpi.name, description: kpi.description || '', type: kpi.type,
        metric_type: kpi.metric_type, unit: kpi.unit || '', input_frequency: kpi.input_frequency,
        target_value: kpi.target_value || undefined, calculation_method: kpi.calculation_method });
    } else {
      setEditingKpi(null);
      setFormData({ name: '', type: 'department', metric_type: 'number', input_frequency: 'monthly' });
    }
    setOpenDialog(true);
  };

  const handleSubmit = async () => {
    try {
      if (editingKpi) {
        await kpisService.update(editingKpi.id, formData);
        enqueueSnackbar('KPI updated successfully', { variant: 'success' });
      } else {
        await kpisService.create(formData);
        enqueueSnackbar('KPI created successfully', { variant: 'success' });
      }
      setOpenDialog(false);
      loadKpis();
    } catch (err: any) {
      enqueueSnackbar(err.response?.data?.message || 'Failed to save KPI', { variant: 'error' });
    }
  };

  const handleDelete = async (id: number, name: string) => {
    if (!confirm(`Delete "${name}"?`)) return;
    try {
      await kpisService.delete(id);
      enqueueSnackbar('KPI deleted', { variant: 'success' });
      loadKpis();
    } catch (err: any) {
      enqueueSnackbar(err.response?.data?.message || 'Failed to delete', { variant: 'error' });
    }
  };

  if (loading) {
    return <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: 400 }}>
      <CircularProgress size={isMobile ? 40 : 60} />
    </Box>;
  }

  return (
    <Box sx={{ pb: isMobile ? 10 : 0 }}>
      <Box sx={{ mb: 3 }}>
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', mb: 2 }}>
          <Box>
            <Typography variant={isMobile ? 'h5' : 'h4'} sx={{ fontWeight: 700, mb: 0.5 }}>
              <AssessmentIcon sx={{ mr: 1, verticalAlign: 'middle', fontSize: 'inherit' }} />
              KPIs
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Define and manage key performance indicators
            </Typography>
          </Box>
          {!isMobile && (
            <Button variant="contained" startIcon={<AddIcon />} onClick={() => handleOpenDialog()} size={isTablet ? 'medium' : 'large'}>
              Add KPI
            </Button>
          )}
        </Box>
        <TextField placeholder="Search KPIs..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)}
          size={isMobile ? 'small' : 'medium'} fullWidth
          InputProps={{ startAdornment: <InputAdornment position="start"><SearchIcon /></InputAdornment> }} />
      </Box>

      {error && <Alert severity="error" sx={{ mb: 3 }} onClose={() => setError(null)}>{error}</Alert>}

      <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider' }}>
        <CardContent sx={{ p: isMobile ? 2 : 3 }}>
          {filteredKpis.length === 0 ? (
            <Box sx={{ textAlign: 'center', py: isMobile ? 4 : 8 }}>
              <AssessmentIcon sx={{ fontSize: isMobile ? 60 : 80, color: 'text.secondary', mb: 2 }} />
              <Typography variant={isMobile ? 'h6' : 'h5'} color="text.secondary" sx={{ mb: 1 }}>
                {searchQuery ? 'No KPIs found' : 'No KPIs yet'}
              </Typography>
              <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                {searchQuery ? 'Try different search terms' : 'Create your first KPI to start tracking performance'}
              </Typography>
              {!searchQuery && (
                <Button variant="contained" startIcon={<AddIcon />} onClick={() => handleOpenDialog()} size={isMobile ? 'medium' : 'large'}>
                  Add KPI
                </Button>
              )}
            </Box>
          ) : isMobile || isTablet ? (
            <Grid container spacing={2}>
              {filteredKpis.map((kpi) => (
                <Grid item xs={12} sm={6} key={kpi.id}>
                  <Paper elevation={0} sx={{ p: 2, border: '1px solid', borderColor: 'divider', borderLeft: `4px solid ${typeColors[kpi.type]}`,
                    transition: 'all 0.2s', '&:hover': { borderColor: typeColors[kpi.type], transform: 'translateY(-2px)',
                    boxShadow: `0 4px 12px ${typeColors[kpi.type]}40` } }}>
                    <Box sx={{ display: 'flex', alignItems: 'flex-start', gap: 2 }}>
                      <Avatar sx={{ bgcolor: `${typeColors[kpi.type]}20`, color: typeColors[kpi.type], width: 48, height: 48 }}>
                        <TrendingUpIcon />
                      </Avatar>
                      <Box sx={{ flex: 1, minWidth: 0 }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 0.5 }}>
                          <Typography variant="subtitle1" sx={{ fontWeight: 600 }}>{kpi.name}</Typography>
                          <Chip label={kpi.is_active ? 'Active' : 'Inactive'} size="small" color={kpi.is_active ? 'success' : 'default'} />
                        </Box>
                        {kpi.description && (
                          <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>{kpi.description}</Typography>
                        )}
                        <Box sx={{ display: 'flex', gap: 1, mb: 2, flexWrap: 'wrap' }}>
                          <Chip label={kpi.type} size="small" sx={{ bgcolor: `${typeColors[kpi.type]}20`, color: typeColors[kpi.type] }} />
                          <Chip label={kpi.metric_type} size="small" sx={{ bgcolor: `${metricColors[kpi.metric_type]}20`, color: metricColors[kpi.metric_type] }} />
                          <Chip label={kpi.input_frequency} size="small" variant="outlined" />
                        </Box>
                        {kpi.target_value && (
                          <Typography variant="caption" color="text.secondary">
                            Target: {kpi.target_value} {kpi.unit}
                          </Typography>
                        )}
                        <Box sx={{ display: 'flex', gap: 1, mt: 2 }}>
                          <Button size="small" variant="outlined" startIcon={<EditIcon fontSize="small" />} onClick={() => handleOpenDialog(kpi)}>Edit</Button>
                          <IconButton size="small" color="error" onClick={() => handleDelete(kpi.id, kpi.name)}><DeleteIcon fontSize="small" /></IconButton>
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
                    <TableCell sx={{ fontWeight: 600 }}>KPI Name</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Type</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Metric</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Frequency</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Target</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Status</TableCell>
                    <TableCell align="right" sx={{ fontWeight: 600 }}>Actions</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {filteredKpis.map((kpi) => (
                    <TableRow key={kpi.id} hover>
                      <TableCell>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                          <Avatar sx={{ bgcolor: `${typeColors[kpi.type]}20`, color: typeColors[kpi.type], width: 40, height: 40 }}>
                            <TrendingUpIcon fontSize="small" />
                          </Avatar>
                          <Box>
                            <Typography variant="body2" sx={{ fontWeight: 600 }}>{kpi.name}</Typography>
                            {kpi.description && <Typography variant="caption" color="text.secondary">{kpi.description}</Typography>}
                          </Box>
                        </Box>
                      </TableCell>
                      <TableCell><Chip label={kpi.type} size="small" sx={{ bgcolor: `${typeColors[kpi.type]}20`, color: typeColors[kpi.type] }} /></TableCell>
                      <TableCell><Chip label={kpi.metric_type} size="small" sx={{ bgcolor: `${metricColors[kpi.metric_type]}20`, color: metricColors[kpi.metric_type] }} /></TableCell>
                      <TableCell>{kpi.input_frequency}</TableCell>
                      <TableCell>{kpi.target_value ? `${kpi.target_value} ${kpi.unit || ''}` : '-'}</TableCell>
                      <TableCell><Chip label={kpi.is_active ? 'Active' : 'Inactive'} color={kpi.is_active ? 'success' : 'default'} size="small" /></TableCell>
                      <TableCell align="right">
                        <Tooltip title="Edit"><IconButton size="small" color="primary" onClick={() => handleOpenDialog(kpi)}><EditIcon fontSize="small" /></IconButton></Tooltip>
                        <Tooltip title="Delete"><IconButton size="small" color="error" onClick={() => handleDelete(kpi.id, kpi.name)}><DeleteIcon fontSize="small" /></IconButton></Tooltip>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </TableContainer>
          )}
          {filteredKpis.length > 0 && (
            <Box sx={{ mt: 2 }}>
              <Typography variant="body2" color="text.secondary">Showing {filteredKpis.length} of {kpis.length} KPIs</Typography>
            </Box>
          )}
        </CardContent>
      </Card>

      {isMobile && <Fab color="primary" aria-label="add" onClick={() => handleOpenDialog()} sx={{ position: 'fixed', bottom: 16, right: 16, boxShadow: 4 }}><AddIcon /></Fab>}

      <Dialog open={openDialog} onClose={() => setOpenDialog(false)} maxWidth="md" fullWidth fullScreen={isMobile}>
        <DialogTitle sx={{ fontWeight: 600 }}>{editingKpi ? 'Edit KPI' : 'Add New KPI'}</DialogTitle>
        <DialogContent>
          <Grid container spacing={2} sx={{ mt: 0.5 }}>
            <Grid item xs={12}><TextField fullWidth label="KPI Name" value={formData.name} onChange={(e) => setFormData({ ...formData, name: e.target.value })} required autoFocus /></Grid>
            <Grid item xs={12}><TextField fullWidth label="Description" value={formData.description || ''} onChange={(e) => setFormData({ ...formData, description: e.target.value })} multiline rows={2} /></Grid>
            <Grid item xs={12} sm={6}>
              <TextField fullWidth select label="Type" value={formData.type} onChange={(e) => setFormData({ ...formData, type: e.target.value as any })}>
                <MenuItem value="department">Department</MenuItem><MenuItem value="position">Position</MenuItem><MenuItem value="personal">Personal</MenuItem>
              </TextField>
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField fullWidth select label="Metric Type" value={formData.metric_type} onChange={(e) => setFormData({ ...formData, metric_type: e.target.value as any })}>
                <MenuItem value="number">Number</MenuItem><MenuItem value="percentage">Percentage</MenuItem>
                <MenuItem value="ratio">Ratio</MenuItem><MenuItem value="custom">Custom</MenuItem><MenuItem value="boolean">Boolean</MenuItem>
              </TextField>
            </Grid>
            <Grid item xs={12} sm={6}><TextField fullWidth label="Unit" value={formData.unit || ''} onChange={(e) => setFormData({ ...formData, unit: e.target.value })} placeholder="e.g. %, units, hours" /></Grid>
            <Grid item xs={12} sm={6}>
              <TextField fullWidth select label="Input Frequency" value={formData.input_frequency} onChange={(e) => setFormData({ ...formData, input_frequency: e.target.value as any })}>
                <MenuItem value="daily">Daily</MenuItem><MenuItem value="weekly">Weekly</MenuItem><MenuItem value="monthly">Monthly</MenuItem>
                <MenuItem value="quarterly">Quarterly</MenuItem><MenuItem value="yearly">Yearly</MenuItem>
              </TextField>
            </Grid>
            <Grid item xs={12} sm={6}><TextField fullWidth label="Target Value" type="number" value={formData.target_value || ''} onChange={(e) => setFormData({ ...formData, target_value: Number(e.target.value) || undefined })} /></Grid>
            <Grid item xs={12} sm={6}>
              <TextField fullWidth select label="Calculation Method" value={formData.calculation_method || 'sum'} onChange={(e) => setFormData({ ...formData, calculation_method: e.target.value as any })}>
                <MenuItem value="sum">Sum</MenuItem><MenuItem value="average">Average</MenuItem><MenuItem value="count">Count</MenuItem>
                <MenuItem value="formula">Formula</MenuItem><MenuItem value="auto">Auto</MenuItem>
              </TextField>
            </Grid>
          </Grid>
        </DialogContent>
        <DialogActions sx={{ px: 3, pb: 3 }}>
          <Button onClick={() => setOpenDialog(false)} color="inherit">Cancel</Button>
          <Button onClick={handleSubmit} variant="contained" disabled={!formData.name}>{editingKpi ? 'Update' : 'Create'}</Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
