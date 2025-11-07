import { useState, useEffect } from 'react';
import {
  Box,
  Typography,
  Card,
  CardContent,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  Chip,
  IconButton,
  Button,
  CircularProgress,
  Alert,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  Grid,
  Avatar,
  Fab,
  useTheme,
  useMediaQuery,
  Tooltip,
  InputAdornment,
} from '@mui/material';
import {
  Edit as EditIcon,
  Delete as DeleteIcon,
  Add as AddIcon,
  Business as BusinessIcon,
  Search as SearchIcon,
} from '@mui/icons-material';
import { useSnackbar } from 'notistack';
import departmentsService, { Department, CreateDepartmentData } from '@/services/departments.service';

export default function DepartmentsPage() {
  const { enqueueSnackbar } = useSnackbar();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  const isTablet = useMediaQuery(theme.breakpoints.down('md'));

  const [departments, setDepartments] = useState<Department[]>([]);
  const [filteredDepartments, setFilteredDepartments] = useState<Department[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [openDialog, setOpenDialog] = useState(false);
  const [editingDepartment, setEditingDepartment] = useState<Department | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [formData, setFormData] = useState<CreateDepartmentData>({
    name: '',
    slug: '',
    description: '',
    color_code: '#1565C0',
    is_active: true,
  });

  useEffect(() => {
    loadDepartments();
  }, []);

  useEffect(() => {
    filterDepartments();
  }, [searchQuery, departments]);

  const loadDepartments = async () => {
    try {
      setLoading(true);
      setError(null);
      const data = await departmentsService.getAll();
      setDepartments(data);
    } catch (err: any) {
      const errorMsg = err.response?.data?.message || 'Failed to load departments';
      setError(errorMsg);
      enqueueSnackbar(errorMsg, { variant: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const filterDepartments = () => {
    if (!searchQuery.trim()) {
      setFilteredDepartments(departments);
      return;
    }

    const query = searchQuery.toLowerCase();
    const filtered = departments.filter(
      (dept) =>
        dept.name.toLowerCase().includes(query) ||
        dept.slug.toLowerCase().includes(query) ||
        (dept.description && dept.description.toLowerCase().includes(query))
    );
    setFilteredDepartments(filtered);
  };

  const handleOpenDialog = (department?: Department) => {
    if (department) {
      setEditingDepartment(department);
      setFormData({
        name: department.name,
        slug: department.slug,
        description: department.description || '',
        color_code: department.color_code,
        is_active: department.is_active,
      });
    } else {
      setEditingDepartment(null);
      setFormData({
        name: '',
        slug: '',
        description: '',
        color_code: '#1565C0',
        is_active: true,
      });
    }
    setOpenDialog(true);
  };

  const handleCloseDialog = () => {
    setOpenDialog(false);
    setEditingDepartment(null);
  };

  const handleSubmit = async () => {
    try {
      if (editingDepartment) {
        await departmentsService.update(editingDepartment.id, formData);
        enqueueSnackbar('Department updated successfully', { variant: 'success' });
      } else {
        await departmentsService.create(formData);
        enqueueSnackbar('Department created successfully', { variant: 'success' });
      }
      handleCloseDialog();
      loadDepartments();
    } catch (err: any) {
      const errorMsg = err.response?.data?.message || 'Failed to save department';
      enqueueSnackbar(errorMsg, { variant: 'error' });
    }
  };

  const handleDelete = async (id: number, name: string) => {
    if (!confirm(`Are you sure you want to delete "${name}"?`)) {
      return;
    }

    try {
      await departmentsService.delete(id);
      enqueueSnackbar('Department deleted successfully', { variant: 'success' });
      loadDepartments();
    } catch (err: any) {
      const errorMsg = err.response?.data?.message || 'Failed to delete department';
      enqueueSnackbar(errorMsg, { variant: 'error' });
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
    <Box sx={{ pb: isMobile ? 10 : 0 }}>
      {/* Header */}
      <Box sx={{ mb: 3 }}>
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', mb: 2 }}>
          <Box>
            <Typography variant={isMobile ? 'h5' : 'h4'} sx={{ fontWeight: 700, mb: 0.5 }}>
              <BusinessIcon sx={{ mr: 1, verticalAlign: 'middle', fontSize: 'inherit' }} />
              Departments
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Manage organizational departments and their configurations
            </Typography>
          </Box>
          {!isMobile && (
            <Button
              variant="contained"
              startIcon={<AddIcon />}
              onClick={() => handleOpenDialog()}
              size={isTablet ? 'medium' : 'large'}
            >
              Add Department
            </Button>
          )}
        </Box>

        {/* Search and Filters */}
        <Box sx={{ display: 'flex', gap: 2, flexWrap: 'wrap' }}>
          <TextField
            placeholder="Search departments..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            size={isMobile ? 'small' : 'medium'}
            sx={{ flex: 1, minWidth: isMobile ? '100%' : 200 }}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <SearchIcon />
                </InputAdornment>
              ),
            }}
          />
        </Box>
      </Box>

      {error && (
        <Alert severity="error" sx={{ mb: 3 }} onClose={() => setError(null)}>
          {error}
        </Alert>
      )}

      {/* Departments Grid/Table */}
      <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider' }}>
        <CardContent sx={{ p: isMobile ? 2 : 3 }}>
          {filteredDepartments.length === 0 ? (
            <Box sx={{ textAlign: 'center', py: isMobile ? 4 : 8 }}>
              <BusinessIcon sx={{ fontSize: isMobile ? 60 : 80, color: 'text.secondary', mb: 2 }} />
              <Typography variant={isMobile ? 'h6' : 'h5'} color="text.secondary" sx={{ mb: 1 }}>
                {searchQuery ? 'No departments found' : 'No departments yet'}
              </Typography>
              <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                {searchQuery
                  ? 'Try adjusting your search criteria'
                  : 'Get started by creating your first department'}
              </Typography>
              {!searchQuery && (
                <Button
                  variant="contained"
                  startIcon={<AddIcon />}
                  onClick={() => handleOpenDialog()}
                  size={isMobile ? 'medium' : 'large'}
                >
                  Add Department
                </Button>
              )}
            </Box>
          ) : isMobile || isTablet ? (
            // Mobile/Tablet: Card View
            <Grid container spacing={2}>
              {filteredDepartments.map((dept) => (
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
                        transform: 'translateY(-2px)',
                        boxShadow: `0 4px 12px ${dept.color_code}40`,
                      },
                    }}
                  >
                    <Box sx={{ display: 'flex', alignItems: 'flex-start', gap: 2 }}>
                      <Avatar
                        sx={{
                          bgcolor: `${dept.color_code}20`,
                          color: dept.color_code,
                          width: 48,
                          height: 48,
                        }}
                      >
                        <BusinessIcon />
                      </Avatar>

                      <Box sx={{ flex: 1, minWidth: 0 }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 0.5 }}>
                          <Typography variant="subtitle1" sx={{ fontWeight: 600 }}>
                            {dept.name}
                          </Typography>
                          <Chip
                            label={dept.is_active ? 'Active' : 'Inactive'}
                            size="small"
                            color={dept.is_active ? 'success' : 'default'}
                          />
                        </Box>

                        <Typography
                          variant="caption"
                          color="text.secondary"
                          sx={{
                            display: 'block',
                            mb: 1,
                            fontFamily: 'monospace',
                            bgcolor: '#f5f5f5',
                            px: 0.5,
                            py: 0.25,
                            borderRadius: 0.5,
                            width: 'fit-content',
                          }}
                        >
                          {dept.slug}
                        </Typography>

                        {dept.description && (
                          <Typography
                            variant="body2"
                            color="text.secondary"
                            sx={{
                              mb: 2,
                              overflow: 'hidden',
                              textOverflow: 'ellipsis',
                              display: '-webkit-box',
                              WebkitLineClamp: 2,
                              WebkitBoxOrient: 'vertical',
                            }}
                          >
                            {dept.description}
                          </Typography>
                        )}

                        <Box sx={{ display: 'flex', gap: 1 }}>
                          <Button
                            size="small"
                            variant="outlined"
                            startIcon={<EditIcon fontSize="small" />}
                            onClick={() => handleOpenDialog(dept)}
                          >
                            Edit
                          </Button>
                          <IconButton
                            size="small"
                            color="error"
                            onClick={() => handleDelete(dept.id, dept.name)}
                          >
                            <DeleteIcon fontSize="small" />
                          </IconButton>
                        </Box>
                      </Box>
                    </Box>
                  </Paper>
                </Grid>
              ))}
            </Grid>
          ) : (
            // Desktop: Table View
            <TableContainer component={Paper} elevation={0}>
              <Table>
                <TableHead>
                  <TableRow>
                    <TableCell sx={{ fontWeight: 600 }}>Department</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Slug</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Description</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Color</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Status</TableCell>
                    <TableCell align="right" sx={{ fontWeight: 600 }}>Actions</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {filteredDepartments.map((dept) => (
                    <TableRow
                      key={dept.id}
                      hover
                      sx={{
                        '&:hover': {
                          bgcolor: `${dept.color_code}08`,
                        },
                      }}
                    >
                      <TableCell>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                          <Avatar
                            sx={{
                              bgcolor: `${dept.color_code}20`,
                              color: dept.color_code,
                              width: 40,
                              height: 40,
                            }}
                          >
                            <BusinessIcon fontSize="small" />
                          </Avatar>
                          <Typography variant="body2" sx={{ fontWeight: 600 }}>
                            {dept.name}
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell>
                        <code
                          style={{
                            background: '#f5f5f5',
                            padding: '4px 8px',
                            borderRadius: 4,
                            fontSize: '0.875rem',
                          }}
                        >
                          {dept.slug}
                        </code>
                      </TableCell>
                      <TableCell>
                        <Typography variant="body2" color="text.secondary" sx={{ maxWidth: 300 }}>
                          {dept.description || '-'}
                        </Typography>
                      </TableCell>
                      <TableCell>
                        <Tooltip title={dept.color_code}>
                          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                            <Box
                              sx={{
                                width: 32,
                                height: 32,
                                borderRadius: 1,
                                bgcolor: dept.color_code,
                                border: '2px solid #fff',
                                boxShadow: `0 2px 8px ${dept.color_code}60`,
                              }}
                            />
                            <Typography variant="caption" sx={{ fontFamily: 'monospace' }}>
                              {dept.color_code}
                            </Typography>
                          </Box>
                        </Tooltip>
                      </TableCell>
                      <TableCell>
                        <Chip
                          label={dept.is_active ? 'Active' : 'Inactive'}
                          color={dept.is_active ? 'success' : 'default'}
                          size="small"
                        />
                      </TableCell>
                      <TableCell align="right">
                        <Tooltip title="Edit">
                          <IconButton
                            size="small"
                            color="primary"
                            onClick={() => handleOpenDialog(dept)}
                          >
                            <EditIcon fontSize="small" />
                          </IconButton>
                        </Tooltip>
                        <Tooltip title="Delete">
                          <IconButton
                            size="small"
                            color="error"
                            onClick={() => handleDelete(dept.id, dept.name)}
                          >
                            <DeleteIcon fontSize="small" />
                          </IconButton>
                        </Tooltip>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </TableContainer>
          )}

          {filteredDepartments.length > 0 && (
            <Box sx={{ mt: 2, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <Typography variant="body2" color="text.secondary">
                Showing {filteredDepartments.length} of {departments.length} departments
              </Typography>
            </Box>
          )}
        </CardContent>
      </Card>

      {/* Floating Action Button for Mobile */}
      {isMobile && (
        <Fab
          color="primary"
          aria-label="add"
          onClick={() => handleOpenDialog()}
          sx={{
            position: 'fixed',
            bottom: 16,
            right: 16,
            boxShadow: 4,
          }}
        >
          <AddIcon />
        </Fab>
      )}

      {/* Add/Edit Dialog */}
      <Dialog
        open={openDialog}
        onClose={handleCloseDialog}
        maxWidth="sm"
        fullWidth
        fullScreen={isMobile}
      >
        <DialogTitle sx={{ fontWeight: 600 }}>
          {editingDepartment ? 'Edit Department' : 'Add New Department'}
        </DialogTitle>
        <DialogContent>
          <Grid container spacing={2} sx={{ mt: 0.5 }}>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Department Name"
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                required
                autoFocus
              />
            </Grid>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Slug"
                value={formData.slug}
                onChange={(e) => setFormData({ ...formData, slug: e.target.value })}
                helperText="URL-friendly identifier (e.g., sales, marketing)"
              />
            </Grid>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Description"
                value={formData.description}
                onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                multiline
                rows={3}
              />
            </Grid>
            <Grid item xs={12}>
              <Typography variant="body2" sx={{ mb: 1, fontWeight: 500 }}>
                Department Color
              </Typography>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                <TextField
                  label="Color Code"
                  value={formData.color_code}
                  onChange={(e) => setFormData({ ...formData, color_code: e.target.value })}
                  sx={{ flex: 1 }}
                />
                <Box
                  sx={{
                    width: 56,
                    height: 56,
                    borderRadius: 2,
                    border: '1px solid',
                    borderColor: 'divider',
                    overflow: 'hidden',
                    cursor: 'pointer',
                  }}
                >
                  <input
                    type="color"
                    value={formData.color_code}
                    onChange={(e) => setFormData({ ...formData, color_code: e.target.value })}
                    style={{ width: '100%', height: '100%', border: 'none', cursor: 'pointer' }}
                  />
                </Box>
              </Box>
            </Grid>
          </Grid>
        </DialogContent>
        <DialogActions sx={{ px: 3, pb: 3 }}>
          <Button onClick={handleCloseDialog} color="inherit">
            Cancel
          </Button>
          <Button
            onClick={handleSubmit}
            variant="contained"
            disabled={!formData.name}
          >
            {editingDepartment ? 'Update' : 'Create'}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
