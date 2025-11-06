import { Box, Typography, Card, CardContent } from '@mui/material';

export default function UsersPage() {
  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 3 }}>Users</Typography>
      <Card><CardContent>
        <Typography>User management page. Backend API ready at /wp-json/kpi/v1/users</Typography>
      </CardContent></Card>
    </Box>
  );
}
