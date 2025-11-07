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
  MenuItem,
  FormControlLabel,
  Switch,
} from '@mui/material';
import {
  Edit as EditIcon,
  Delete as DeleteIcon,
  Add as AddIcon,
  Person as PersonIcon,
  Search as SearchIcon,
  VpnKey as KeyIcon,
} from '@mui/icons-material';
import { useSnackbar } from 'notistack';
import usersService, { User, CreateUserData, UpdateUserData } from '@/services/users.service';
import departmentsService, { Department } from '@/services/departments.service';

const roleColors = {
  super_admin: '#D32F2F',
  dept_head: '#ED6C02',
  manager: '#1976D2',
  staff: '#388E3C',
};

const roleLabels = {
  super_admin: 'Super Admin',
  dept_head: 'Department Head',
  manager: 'Manager',
  staff: 'Staff',
};

export default function UsersPage() {
  const { enqueueSnackbar } = useSnackbar();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  const isTablet = useMediaQuery(theme.breakpoints.down('md'));

  const [users, setUsers] = useState<User[]>([]);
  const [filteredUsers, setFilteredUsers] = useState<User[]>([]);
  const [departments, setDepartments] = useState<Department[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [openDialog, setOpenDialog] = useState(false);
  const [editingUser, setEditingUser] = useState<User | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [formData, setFormData] = useState<CreateUserData>({
    username: '',
    email: '',
    password: '',
    full_name: '',
    role: 'staff',
    is_active: true,
  });

  useEffect(() => {
    loadData();
  }, []);

  useEffect(() => {
    filterUsers();
  }, [searchQuery, users]);

  const loadData = async () => {
    try {
      setLoading(true);
      setError(null);
      const [usersData, deptsData] = await Promise.all([
        usersService.getAll(),
        departmentsService.getAll(),
      ]);
      setUsers(usersData);
      setDepartments(deptsData);
    } catch (err: any) {
      const errorMsg = err.response?.data?.message || 'Failed to load users';
      setError(errorMsg);
      enqueueSnackbar(errorMsg, { variant: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const filterUsers = () => {
    if (!searchQuery.trim()) {
      setFilteredUsers(users);
      return;
    }

    const query = searchQuery.toLowerCase();
    const filtered = users.filter(
      (user) =>
        user.username.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        user.full_name.toLowerCase().includes(query)
    );
    setFilteredUsers(filtered);
  };

  const handleOpenDialog = (user?: User) => {
    if (user) {
      setEditingUser(user);
      setFormData({
        username: user.username,
        email: user.email,
        password: '',
        full_name: user.full_name,
        role: user.role,
        department_id: user.department_id || undefined,
        position_id: user.position_id || undefined,
        is_active: user.is_active,
      });
    } else {
      setEditingUser(null);
      setFormData({
        username: '',
        email: '',
        password: '',
        full_name: '',
        role: 'staff',
        is_active: true,
      });
    }
    setOpenDialog(true);
  };

  const handleCloseDialog = () => {
    setOpenDialog(false);
    setEditingUser(null);
  };

  const handleSubmit = async () => {
    try {
      if (editingUser) {
        const updateData: UpdateUserData = {
          email: formData.email,
          full_name: formData.full_name,
          role: formData.role,
          department_id: formData.department_id,
          position_id: formData.position_id,
          is_active: formData.is_active,
        };
        if (formData.password) {
          updateData.password = formData.password;
        }
        await usersService.update(editingUser.id, updateData);
        enqueueSnackbar('User updated successfully', { variant: 'success' });
      } else {
        await usersService.create(formData);
        enqueueSnackbar('User created successfully', { variant: 'success' });
      }
      handleCloseDialog();
      loadData();
    } catch (err: any) {
      const errorMsg = err.response?.data?.message || 'Failed to save user';
      enqueueSnackbar(errorMsg, { variant: 'error' });
    }
  };

  const handleDelete = async (id: number, name: string) => {
    if (!confirm(`Are you sure you want to delete "${name}"?`)) {
      return;
    }

    try {
      await usersService.delete(id);
      enqueueSnackbar('User deleted successfully', { variant: 'success' });
      loadData();
    } catch (err: any) {
      const errorMsg = err.response?.data?.message || 'Failed to delete user';
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
              <PersonIcon sx={{ mr: 1, verticalAlign: 'middle', fontSize: 'inherit' }} />
              Users
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Manage user accounts, roles, and permissions
            </Typography>
          </Box>
          {!isMobile && (
            <Button
              variant="contained"
              startIcon={<AddIcon />}
              onClick={() => handleOpenDialog()}
              size={isTablet ? 'medium' : 'large'}
            >
              Add User
            </Button>
          )}
        </Box>

        {/* Search */}
        <TextField
          placeholder="Search users..."
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          size={isMobile ? 'small' : 'medium'}
          fullWidth
          InputProps={{
            startAdornment: (
              <InputAdornment position="start">
                <SearchIcon />
              </InputAdornment>
            ),
          }}
        />
      </Box>

      {error && (
        <Alert severity="error" sx={{ mb: 3 }} onClose={() => setError(null)}>
          {error}
        </Alert>
      )}

      {/* Users Table/Grid */}
      <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider' }}>
        <CardContent sx={{ p: isMobile ? 2 : 3 }}>
          {filteredUsers.length === 0 ? (
            <Box sx={{ textAlign: 'center', py: isMobile ? 4 : 8 }}>
              <PersonIcon sx={{ fontSize: isMobile ? 60 : 80, color: 'text.secondary', mb: 2 }} />
              <Typography variant={isMobile ? 'h6' : 'h5'} color="text.secondary" sx={{ mb: 1 }}>
                {searchQuery ? 'No users found' : 'No users yet'}
              </Typography>
              <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                {searchQuery ? 'Try adjusting your search criteria' : 'Get started by creating your first user'}
              </Typography>
              {!searchQuery && (
                <Button
                  variant="contained"
                  startIcon={<AddIcon />}
                  onClick={() => handleOpenDialog()}
                  size={isMobile ? 'medium' : 'large'}
                >
                  Add User
                </Button>
              )}
            </Box>
          ) : isMobile || isTablet ? (
            // Mobile/Tablet: Card View
            <Grid container spacing={2}>
              {filteredUsers.map((user) => (
                <Grid item xs={12} sm={6} key={user.id}>
                  <Paper
                    elevation={0}
                    sx={{
                      p: 2,
                      border: '1px solid',
                      borderColor: 'divider',
                      borderLeft: `4px solid ${roleColors[user.role]}`,
                      transition: 'all 0.2s',
                      '&:hover': {
                        borderColor: roleColors[user.role],
                        transform: 'translateY(-2px)',
                        boxShadow: `0 4px 12px ${roleColors[user.role]}40`,
                      },
                    }}
                  >
                    <Box sx={{ display: 'flex', alignItems: 'flex-start', gap: 2 }}>
                      <Avatar
                        sx={{
                          bgcolor: `${roleColors[user.role]}20`,
                          color: roleColors[user.role],
                          width: 48,
                          height: 48,
                        }}
                      >
                        {user.full_name.charAt(0).toUpperCase()}
                      </Avatar>

                      <Box sx={{ flex: 1, minWidth: 0 }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 0.5 }}>
                          <Typography variant="subtitle1" sx={{ fontWeight: 600 }}>
                            {user.full_name}
                          </Typography>
                          <Chip
                            label={user.is_active ? 'Active' : 'Inactive'}
                            size="small"
                            color={user.is_active ? 'success' : 'default'}
                          />
                        </Box>

                        <Typography variant="body2" color="text.secondary" sx={{ mb: 0.5 }}>
                          @{user.username} • {user.email}
                        </Typography>

                        <Chip
                          label={roleLabels[user.role]}
                          size="small"
                          sx={{
                            bgcolor: `${roleColors[user.role]}20`,
                            color: roleColors[user.role],
                            mb: 2,
                          }}
                        />

                        <Box sx={{ display: 'flex', gap: 1 }}>
                          <Button
                            size="small"
                            variant="outlined"
                            startIcon={<EditIcon fontSize="small" />}
                            onClick={() => handleOpenDialog(user)}
                          >
                            Edit
                          </Button>
                          <IconButton
                            size="small"
                            color="error"
                            onClick={() => handleDelete(user.id, user.full_name)}
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
                    <TableCell sx={{ fontWeight: 600 }}>User</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Email</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Role</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Department</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Status</TableCell>
                    <TableCell align="right" sx={{ fontWeight: 600 }}>Actions</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {filteredUsers.map((user) => (
                    <TableRow key={user.id} hover>
                      <TableCell>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                          <Avatar
                            sx={{
                              bgcolor: `${roleColors[user.role]}20`,
                              color: roleColors[user.role],
                              width: 40,
                              height: 40,
                            }}
                          >
                            {user.full_name.charAt(0).toUpperCase()}
                          </Avatar>
                          <Box>
                            <Typography variant="body2" sx={{ fontWeight: 600 }}>
                              {user.full_name}
                            </Typography>
                            <Typography variant="caption" color="text.secondary">
                              @{user.username}
                            </Typography>
                          </Box>
                        </Box>
                      </TableCell>
                      <TableCell>{user.email}</TableCell>
                      <TableCell>
                        <Chip
                          label={roleLabels[user.role]}
                          size="small"
                          sx={{
                            bgcolor: `${roleColors[user.role]}20`,
                            color: roleColors[user.role],
                          }}
                        />
                      </TableCell>
                      <TableCell>
                        {user.department_name || '-'}
                      </TableCell>
                      <TableCell>
                        <Chip
                          label={user.is_active ? 'Active' : 'Inactive'}
                          color={user.is_active ? 'success' : 'default'}
                          size="small"
                        />
                      </TableCell>
                      <TableCell align="right">
                        <Tooltip title="Edit">
                          <IconButton
                            size="small"
                            color="primary"
                            onClick={() => handleOpenDialog(user)}
                          >
                            <EditIcon fontSize="small" />
                          </IconButton>
                        </Tooltip>
                        <Tooltip title="Delete">
                          <IconButton
                            size="small"
                            color="error"
                            onClick={() => handleDelete(user.id, user.full_name)}
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

          {filteredUsers.length > 0 && (
            <Box sx={{ mt: 2, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <Typography variant="body2" color="text.secondary">
                Showing {filteredUsers.length} of {users.length} users
              </Typography>
            </Box>
          )}
        </CardContent>
      </Card>

      {/* FAB for Mobile */}
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
      <Dialog open={openDialog} onClose={handleCloseDialog} maxWidth="sm" fullWidth fullScreen={isMobile}>
        <DialogTitle sx={{ fontWeight: 600 }}>
          {editingUser ? 'Edit User' : 'Add New User'}
        </DialogTitle>
        <DialogContent>
          <Grid container spacing={2} sx={{ mt: 0.5 }}>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Full Name"
                value={formData.full_name}
                onChange={(e) => setFormData({ ...formData, full_name: e.target.value })}
                required
                autoFocus
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Username"
                value={formData.username}
                onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                required
                disabled={!!editingUser}
                helperText={editingUser ? 'Username cannot be changed' : ''}
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Email"
                type="email"
                value={formData.email}
                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                required
              />
            </Grid>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label={editingUser ? 'New Password (leave blank to keep current)' : 'Password'}
                type="password"
                value={formData.password}
                onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                required={!editingUser}
                InputProps={{
                  startAdornment: (
                    <InputAdornment position="start">
                      <KeyIcon />
                    </InputAdornment>
                  ),
                }}
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                select
                label="Role"
                value={formData.role}
                onChange={(e) => setFormData({ ...formData, role: e.target.value as any })}
              >
                <MenuItem value="staff">Staff</MenuItem>
                <MenuItem value="manager">Manager</MenuItem>
                <MenuItem value="dept_head">Department Head</MenuItem>
                <MenuItem value="super_admin">Super Admin</MenuItem>
              </TextField>
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                select
                label="Department"
                value={formData.department_id || ''}
                onChange={(e) => setFormData({ ...formData, department_id: Number(e.target.value) || undefined })}
              >
                <MenuItem value="">None</MenuItem>
                {departments.map((dept) => (
                  <MenuItem key={dept.id} value={dept.id}>
                    {dept.name}
                  </MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid item xs={12}>
              <FormControlLabel
                control={
                  <Switch
                    checked={formData.is_active}
                    onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                  />
                }
                label="Active"
              />
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
            disabled={!formData.username || !formData.email || !formData.full_name || (!editingUser && !formData.password)}
          >
            {editingUser ? 'Update' : 'Create'}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
