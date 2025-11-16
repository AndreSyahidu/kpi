import { Skeleton, Box, Card, CardContent, Stack, Grid } from '@mui/material';

/**
 * Loading skeleton for table rows
 */
export function TableRowSkeleton({ columns = 5, rows = 5 }) {
  return (
    <>
      {Array.from({ length: rows }).map((_, rowIndex) => (
        <Box
          key={rowIndex}
          sx={{
            display: 'flex',
            gap: 2,
            p: 2,
            borderBottom: '1px solid',
            borderColor: 'divider',
          }}
        >
          {Array.from({ length: columns }).map((_, colIndex) => (
            <Skeleton
              key={colIndex}
              variant="text"
              width={colIndex === 0 ? 60 : colIndex === columns - 1 ? 100 : '100%'}
              height={24}
            />
          ))}
        </Box>
      ))}
    </>
  );
}

/**
 * Loading skeleton for cards/grid layout
 */
export function CardGridSkeleton({ count = 6 }) {
  return (
    <Grid container spacing={3}>
      {Array.from({ length: count }).map((_, index) => (
        <Grid item xs={12} sm={6} md={4} key={index}>
          <Card>
            <CardContent>
              <Skeleton variant="text" width="60%" height={32} sx={{ mb: 1 }} />
              <Skeleton variant="text" width="100%" height={20} />
              <Skeleton variant="text" width="80%" height={20} sx={{ mb: 2 }} />
              <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 2 }}>
                <Skeleton variant="rectangular" width={80} height={32} />
                <Skeleton variant="circular" width={40} height={40} />
              </Box>
            </CardContent>
          </Card>
        </Grid>
      ))}
    </Grid>
  );
}

/**
 * Loading skeleton for dashboard stats cards
 */
export function StatsCardSkeleton({ count = 4 }) {
  return (
    <Grid container spacing={3}>
      {Array.from({ length: count }).map((_, index) => (
        <Grid item xs={12} sm={6} md={3} key={index}>
          <Card>
            <CardContent>
              <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <Box>
                  <Skeleton variant="text" width={100} height={20} />
                  <Skeleton variant="text" width={80} height={48} sx={{ mt: 1 }} />
                  <Skeleton variant="text" width={120} height={16} />
                </Box>
                <Skeleton variant="circular" width={56} height={56} />
              </Box>
            </CardContent>
          </Card>
        </Grid>
      ))}
    </Grid>
  );
}

/**
 * Loading skeleton for charts
 */
export function ChartSkeleton({ height = 300 }) {
  return (
    <Card>
      <CardContent>
        <Skeleton variant="text" width="30%" height={32} sx={{ mb: 2 }} />
        <Skeleton variant="rectangular" width="100%" height={height} sx={{ borderRadius: 1 }} />
        <Box sx={{ display: 'flex', justifyContent: 'center', gap: 2, mt: 2 }}>
          {Array.from({ length: 4 }).map((_, index) => (
            <Skeleton key={index} variant="text" width={60} height={20} />
          ))}
        </Box>
      </CardContent>
    </Card>
  );
}

/**
 * Loading skeleton for form
 */
export function FormSkeleton({ fields = 6 }) {
  return (
    <Card>
      <CardContent>
        <Skeleton variant="text" width="40%" height={36} sx={{ mb: 3 }} />
        <Stack spacing={3}>
          {Array.from({ length: fields }).map((_, index) => (
            <Box key={index}>
              <Skeleton variant="text" width={120} height={20} sx={{ mb: 1 }} />
              <Skeleton variant="rectangular" width="100%" height={56} sx={{ borderRadius: 1 }} />
            </Box>
          ))}
          <Box sx={{ display: 'flex', gap: 2, justifyContent: 'flex-end', mt: 2 }}>
            <Skeleton variant="rectangular" width={100} height={40} sx={{ borderRadius: 1 }} />
            <Skeleton variant="rectangular" width={100} height={40} sx={{ borderRadius: 1 }} />
          </Box>
        </Stack>
      </CardContent>
    </Card>
  );
}

/**
 * Loading skeleton for list items
 */
export function ListSkeleton({ count = 5, avatar = false }) {
  return (
    <Stack spacing={1}>
      {Array.from({ length: count }).map((_, index) => (
        <Box
          key={index}
          sx={{
            display: 'flex',
            alignItems: 'center',
            gap: 2,
            p: 2,
            borderBottom: '1px solid',
            borderColor: 'divider',
          }}
        >
          {avatar && <Skeleton variant="circular" width={40} height={40} />}
          <Box sx={{ flex: 1 }}>
            <Skeleton variant="text" width="60%" height={24} />
            <Skeleton variant="text" width="40%" height={20} />
          </Box>
          <Skeleton variant="rectangular" width={80} height={32} sx={{ borderRadius: 1 }} />
        </Box>
      ))}
    </Stack>
  );
}

/**
 * Loading skeleton for detail page
 */
export function DetailSkeleton() {
  return (
    <Box>
      {/* Header */}
      <Box sx={{ mb: 3 }}>
        <Skeleton variant="text" width="40%" height={48} sx={{ mb: 1 }} />
        <Skeleton variant="text" width="60%" height={24} />
      </Box>

      {/* Main content */}
      <Grid container spacing={3}>
        {/* Left column */}
        <Grid item xs={12} md={8}>
          <Card sx={{ mb: 3 }}>
            <CardContent>
              <Skeleton variant="text" width="30%" height={32} sx={{ mb: 2 }} />
              <Stack spacing={2}>
                {Array.from({ length: 5 }).map((_, index) => (
                  <Box key={index}>
                    <Skeleton variant="text" width={100} height={20} sx={{ mb: 0.5 }} />
                    <Skeleton variant="text" width="80%" height={24} />
                  </Box>
                ))}
              </Stack>
            </CardContent>
          </Card>

          {/* Chart */}
          <ChartSkeleton height={250} />
        </Grid>

        {/* Right column - sidebar */}
        <Grid item xs={12} md={4}>
          <Card>
            <CardContent>
              <Skeleton variant="text" width="50%" height={24} sx={{ mb: 2 }} />
              <Stack spacing={2}>
                {Array.from({ length: 4 }).map((_, index) => (
                  <Box key={index}>
                    <Skeleton variant="text" width={80} height={20} />
                    <Skeleton variant="text" width="100%" height={24} />
                  </Box>
                ))}
              </Stack>
            </CardContent>
          </Card>
        </Grid>
      </Grid>
    </Box>
  );
}

/**
 * Loading skeleton for data table with filters
 */
export function DataTableSkeleton({ hasFilters = true }) {
  return (
    <Box>
      {/* Filters */}
      {hasFilters && (
        <Card sx={{ mb: 3 }}>
          <CardContent>
            <Grid container spacing={2}>
              {Array.from({ length: 4 }).map((_, index) => (
                <Grid item xs={12} sm={6} md={3} key={index}>
                  <Skeleton variant="rectangular" width="100%" height={56} sx={{ borderRadius: 1 }} />
                </Grid>
              ))}
            </Grid>
          </CardContent>
        </Card>
      )}

      {/* Table header */}
      <Card>
        <Box sx={{ p: 2, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <Skeleton variant="text" width={200} height={32} />
          <Box sx={{ display: 'flex', gap: 2 }}>
            <Skeleton variant="rectangular" width={120} height={40} sx={{ borderRadius: 1 }} />
            <Skeleton variant="rectangular" width={100} height={40} sx={{ borderRadius: 1 }} />
          </Box>
        </Box>

        {/* Table rows */}
        <TableRowSkeleton columns={6} rows={8} />

        {/* Pagination */}
        <Box sx={{ p: 2, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <Skeleton variant="text" width={150} height={24} />
          <Box sx={{ display: 'flex', gap: 1 }}>
            {Array.from({ length: 5 }).map((_, index) => (
              <Skeleton key={index} variant="rectangular" width={40} height={32} sx={{ borderRadius: 1 }} />
            ))}
          </Box>
        </Box>
      </Card>
    </Box>
  );
}

/**
 * Generic loading skeleton
 */
export function LoadingSkeleton({ variant = 'table' }) {
  switch (variant) {
    case 'table':
      return <DataTableSkeleton />;
    case 'cards':
      return <CardGridSkeleton />;
    case 'stats':
      return <StatsCardSkeleton />;
    case 'chart':
      return <ChartSkeleton />;
    case 'form':
      return <FormSkeleton />;
    case 'list':
      return <ListSkeleton />;
    case 'detail':
      return <DetailSkeleton />;
    default:
      return <Skeleton variant="rectangular" width="100%" height={400} />;
  }
}
