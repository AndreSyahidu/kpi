import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Box, Card, CardContent, TextField, Button, Typography, Alert } from '@mui/material';
import { useAuthStore } from '@/store/authStore';
import { authService } from '@/services/auth.service';
import { APP_CONFIG } from '@/config/api.config';

export default function LoginPage() {
  const navigate = useNavigate();
  const setAuth = useAuthStore((state) => state.setAuth);
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const response = await authService.login(username, password);
      if (response.success && response.data) {
        setAuth(response.data.user, response.data.access_token, response.data.refresh_token);
        navigate('/');
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Login failed. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', minHeight: '100vh', bgcolor: 'background.default' }}>
      <Card sx={{ maxWidth: 400, width: '100%', m: 2 }}>
        <CardContent sx={{ p: 4 }}>
          <Box sx={{ textAlign: 'center', mb: 3 }}>
            {APP_CONFIG.companyLogo && <img src={APP_CONFIG.companyLogo} alt="Logo" style={{ width: 80, marginBottom: 16 }} />}
            <Typography variant="h5" fontWeight={600}>{APP_CONFIG.companyName}</Typography>
            <Typography variant="body2" color="text.secondary">KPI Dashboard</Typography>
          </Box>

          <form onSubmit={handleSubmit}>
            {error && <Alert severity="error" sx={{ mb: 2 }}>{error}</Alert>}
            
            <TextField fullWidth label="Username" value={username} onChange={(e) => setUsername(e.target.value)} margin="normal" required autoFocus />
            <TextField fullWidth label="Password" type="password" value={password} onChange={(e) => setPassword(e.target.value)} margin="normal" required />
            
            <Button type="submit" fullWidth variant="contained" size="large" disabled={loading} sx={{ mt: 3 }}>
              {loading ? 'Signing in...' : 'Sign In'}
            </Button>
          </form>

          <Typography variant="caption" color="text.secondary" sx={{ mt: 3, display: 'block', textAlign: 'center' }}>
            Default: admin / admin
          </Typography>
        </CardContent>
      </Card>
    </Box>
  );
}
