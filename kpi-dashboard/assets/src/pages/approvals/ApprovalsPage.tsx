import { useState, useEffect } from 'react';
import {
  Box, Typography, Card, CardContent, Grid, TextField, Button, Table, TableBody,
  TableCell, TableContainer, TableHead, TableRow, Paper, Chip, IconButton,
  Dialog, DialogTitle, DialogContent, DialogActions, Alert, CircularProgress,
 useTheme, useMediaQuery, Avatar, Tooltip, Tab, Tabs, Badge,
} from '@mui/material';
import {
  CheckCircle as ApproveIcon, Cancel as RejectIcon, Pending as PendingIcon,
  Assessment as AssessmentIcon, TrendingUp as TrendingUpIcon, Person as PersonIcon,
  CalendarToday as CalendarIcon, Info as InfoIcon,
} from '@mui/icons-material';
import { useSnackbar } from 'notistack';
import dayjs from 'dayjs';
import approvalsService, { ApprovalItem } from '@/services/approvals.service';

const statusColors = {
  submitted: '#1976D2',
  approved: '#2E7D32',
  rejected: '#D32F2F',
};

interface TabPanelProps {
  children?: React.ReactNode;
  index: number;
  value: number;
}

const TabPanel = ({ children, value, index }: TabPanelProps) => (
  <div role="tabpanel" hidden={value !== index}>
    {value === index && <Box sx={{ mt: 3 }}>{children}</Box>}
  </div>
);

export default function ApprovalsPage() {
  const { enqueueSnackbar } = useSnackbar();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  const isTablet = useMediaQuery(theme.breakpoints.down('md'));
  const [activeTab, setActiveTab] = useState(0);
  const [approvals, setApprovals] = useState<ApprovalItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [processing, setProcessing] = useState<number | null>(null);

  // Review dialog
  const [openReviewDialog, setOpenReviewDialog] = useState(false);
  const [reviewingItem, setReviewingItem] = useState<ApprovalItem | null>(null);
  const [reviewNotes, setReviewNotes] = useState('');
  const [reviewAction, setReviewAction] = useState<'approve' | 'reject'>('approve');

  useEffect(() => {
    loadApprovals();
  }, []);

  const loadApprovals = async () => {
    try {
      setLoading(true);
      const data = await approvalsService.getPending();
      setApprovals(data);
    } catch (err: any) {
      const errorMsg = err.response?.data?.message || 'Failed to load approvals';
      enqueueSnackbar(errorMsg, { variant: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const handleOpenReview = (item: ApprovalItem, action: 'approve' | 'reject') => {
    setReviewingItem(item);
    setReviewAction(action);
    setReviewNotes('');
    setOpenReviewDialog(true);
  };

  const handleReview = async () => {
    if (!reviewingItem) return;

    try {
      setProcessing(reviewingItem.id);

      if (reviewAction === 'approve') {
        await approvalsService.approve(reviewingItem.id, reviewNotes);
        enqueueSnackbar('Data entry approved successfully', { variant: 'success' });
      } else {
        if (!reviewNotes.trim()) {
          enqueueSnackbar('Please provide a reason for rejection', { variant: 'warning' });
          setProcessing(null);
          return;
        }
        await approvalsService.reject(reviewingItem.id, reviewNotes);
        enqueueSnackbar('Data entry rejected', { variant: 'info' });
      }

      setOpenReviewDialog(false);
      setReviewingItem(null);
      setReviewNotes('');
      loadApprovals();
    } catch (err: any) {
      enqueueSnackbar(err.response?.data?.message || 'Failed to process approval', { variant: 'error' });
    } finally {
      setProcessing(null);
    }
  };

  const pendingApprovals = approvals.filter((a) => a.status === 'submitted');
  const reviewedApprovals = approvals.filter((a) => a.status === 'approved' || a.status === 'rejected');

  const renderApprovalCard = (item: ApprovalItem) => (
    <Paper
      elevation={0}
      sx={{
        p: 2,
        border: '1px solid',
        borderColor: 'divider',
        borderLeft: `4px solid ${statusColors[item.status]}`,
        transition: 'all 0.2s',
        '&:hover': {
          borderColor: statusColors[item.status],
          transform: 'translateY(-2px)',
          boxShadow: `0 4px 12px ${statusColors[item.status]}40`,
        },
      }}
    >
      <Box sx={{ display: 'flex', alignItems: 'flex-start', gap: 2 }}>
        <Avatar
          sx={{
            bgcolor: `${statusColors[item.status]}20`,
            color: statusColors[item.status],
            width: 48,
            height: 48,
          }}
        >
          <TrendingUpIcon />
        </Avatar>
        <Box sx={{ flex: 1, minWidth: 0 }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 0.5 }}>
            <Typography variant="subtitle1" sx={{ fontWeight: 600 }}>
              {item.kpi_name}
            </Typography>
          </Box>
          <Typography variant="h6" sx={{ color: statusColors[item.status], fontWeight: 700, mb: 1 }}>
            {item.value} {item.unit}
          </Typography>

          <Box sx={{ display: 'flex', gap: 1, mb: 1, flexWrap: 'wrap' }}>
            <Chip
              label={item.status}
              size="small"
              sx={{
                bgcolor: `${statusColors[item.status]}20`,
                color: statusColors[item.status],
                textTransform: 'capitalize',
              }}
            />
            <Chip
              icon={<CalendarIcon fontSize="small" />}
              label={`${dayjs(item.period_start).format('MMM DD')} - ${dayjs(item.period_end).format('MMM DD')}`}
              size="small"
              variant="outlined"
            />
          </Box>

          <Box sx={{ mb: 1 }}>
            <Typography variant="caption" color="text.secondary" sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
              <PersonIcon fontSize="small" />
              Submitted by: {item.submitted_by_name || 'Unknown'}
            </Typography>
            <Typography variant="caption" color="text.secondary" sx={{ display: 'block' }}>
              on {dayjs(item.submitted_at).format('MMM DD, YYYY HH:mm')}
            </Typography>
          </Box>

          {item.notes && (
            <Typography variant="body2" color="text.secondary" sx={{ mb: 1, fontStyle: 'italic' }}>
              "{item.notes}"
            </Typography>
          )}

          {item.status === 'submitted' && (
            <Box sx={{ display: 'flex', gap: 1, mt: 2 }}>
              <Button
                size="small"
                variant="contained"
                color="success"
                startIcon={<ApproveIcon fontSize="small" />}
                onClick={() => handleOpenReview(item, 'approve')}
                disabled={processing === item.id}
              >
                Approve
              </Button>
              <Button
                size="small"
                variant="outlined"
                color="error"
                startIcon={<RejectIcon fontSize="small" />}
                onClick={() => handleOpenReview(item, 'reject')}
                disabled={processing === item.id}
              >
                Reject
              </Button>
            </Box>
          )}

          {item.status !== 'submitted' && item.review_notes && (
            <Alert severity={item.status === 'approved' ? 'success' : 'error'} sx={{ mt: 1 }}>
              <Typography variant="caption" sx={{ fontWeight: 600 }}>
                Review Notes:
              </Typography>
              <Typography variant="body2">{item.review_notes}</Typography>
              <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mt: 0.5 }}>
                Reviewed by {item.reviewed_by_name || 'Unknown'} on {dayjs(item.reviewed_at).format('MMM DD, YYYY')}
              </Typography>
            </Alert>
          )}
        </Box>
      </Box>
    </Paper>
  );

  const renderApprovalTable = (items: ApprovalItem[]) => (
    <TableContainer component={Paper} elevation={0}>
      <Table>
        <TableHead>
          <TableRow>
            <TableCell sx={{ fontWeight: 600 }}>KPI</TableCell>
            <TableCell sx={{ fontWeight: 600 }}>Value</TableCell>
            <TableCell sx={{ fontWeight: 600 }}>Period</TableCell>
            <TableCell sx={{ fontWeight: 600 }}>Submitted By</TableCell>
            <TableCell sx={{ fontWeight: 600 }}>Status</TableCell>
            <TableCell align="right" sx={{ fontWeight: 600 }}>Actions</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {items.map((item) => (
            <TableRow key={item.id} hover>
              <TableCell>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                  <Avatar
                    sx={{
                      bgcolor: `${statusColors[item.status]}20`,
                      color: statusColors[item.status],
                      width: 40,
                      height: 40,
                    }}
                  >
                    <TrendingUpIcon fontSize="small" />
                  </Avatar>
                  <Box>
                    <Typography variant="body2" sx={{ fontWeight: 600 }}>
                      {item.kpi_name}
                    </Typography>
                    {item.notes && (
                      <Typography variant="caption" color="text.secondary" sx={{ fontStyle: 'italic' }}>
                        "{item.notes}"
                      </Typography>
                    )}
                  </Box>
                </Box>
              </TableCell>
              <TableCell>
                <Typography variant="body2" sx={{ fontWeight: 700, color: statusColors[item.status] }}>
                  {item.value} {item.unit}
                </Typography>
              </TableCell>
              <TableCell>
                <Typography variant="body2">{dayjs(item.period_start).format('MMM DD, YYYY')}</Typography>
                <Typography variant="caption" color="text.secondary">
                  to {dayjs(item.period_end).format('MMM DD, YYYY')}
                </Typography>
              </TableCell>
              <TableCell>
                <Typography variant="body2">{item.submitted_by_name || 'Unknown'}</Typography>
                <Typography variant="caption" color="text.secondary">
                  {dayjs(item.submitted_at).format('MMM DD, YYYY')}
                </Typography>
              </TableCell>
              <TableCell>
                <Chip
                  label={item.status}
                  size="small"
                  sx={{
                    bgcolor: `${statusColors[item.status]}20`,
                    color: statusColors[item.status],
                    textTransform: 'capitalize',
                  }}
                />
              </TableCell>
              <TableCell align="right">
                {item.status === 'submitted' && (
                  <>
                    <Tooltip title="Approve">
                      <IconButton
                        size="small"
                        color="success"
                        onClick={() => handleOpenReview(item, 'approve')}
                        disabled={processing === item.id}
                      >
                        <ApproveIcon fontSize="small" />
                      </IconButton>
                    </Tooltip>
                    <Tooltip title="Reject">
                      <IconButton
                        size="small"
                        color="error"
                        onClick={() => handleOpenReview(item, 'reject')}
                        disabled={processing === item.id}
                      >
                        <RejectIcon fontSize="small" />
                      </IconButton>
                    </Tooltip>
                  </>
                )}
                {item.status !== 'submitted' && item.review_notes && (
                  <Tooltip title={item.review_notes}>
                    <IconButton size="small">
                      <InfoIcon fontSize="small" />
                    </IconButton>
                  </Tooltip>
                )}
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </TableContainer>
  );

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
          <PendingIcon sx={{ mr: 1, verticalAlign: 'middle', fontSize: 'inherit' }} />
          Approvals
        </Typography>
        <Typography variant="body2" color="text.secondary">
          Review and approve submitted KPI data entries
        </Typography>
      </Box>

      <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider' }}>
        <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
          <Tabs value={activeTab} onChange={(_, newValue) => setActiveTab(newValue)} variant={isMobile ? 'fullWidth' : 'standard'}>
            <Tab
              icon={
                <Badge badgeContent={pendingApprovals.length} color="primary">
                  <PendingIcon />
                </Badge>
              }
              label="Pending"
              iconPosition="start"
            />
            <Tab
              icon={<AssessmentIcon />}
              label="Reviewed"
              iconPosition="start"
            />
          </Tabs>
        </Box>

        <CardContent sx={{ p: isMobile ? 2 : 3 }}>
          {/* Pending Tab */}
          <TabPanel value={activeTab} index={0}>
            {pendingApprovals.length === 0 ? (
              <Box sx={{ textAlign: 'center', py: isMobile ? 4 : 8 }}>
                <PendingIcon sx={{ fontSize: isMobile ? 60 : 80, color: 'text.secondary', mb: 2 }} />
                <Typography variant={isMobile ? 'h6' : 'h5'} color="text.secondary" sx={{ mb: 1 }}>
                  No pending approvals
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  All submitted data entries have been reviewed
                </Typography>
              </Box>
            ) : isMobile || isTablet ? (
              <Grid container spacing={2}>
                {pendingApprovals.map((item) => (
                  <Grid item xs={12} sm={6} key={item.id}>
                    {renderApprovalCard(item)}
                  </Grid>
                ))}
              </Grid>
            ) : (
              renderApprovalTable(pendingApprovals)
            )}
          </TabPanel>

          {/* Reviewed Tab */}
          <TabPanel value={activeTab} index={1}>
            {reviewedApprovals.length === 0 ? (
              <Box sx={{ textAlign: 'center', py: isMobile ? 4 : 8 }}>
                <AssessmentIcon sx={{ fontSize: isMobile ? 60 : 80, color: 'text.secondary', mb: 2 }} />
                <Typography variant={isMobile ? 'h6' : 'h5'} color="text.secondary" sx={{ mb: 1 }}>
                  No reviewed items yet
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Approved and rejected entries will appear here
                </Typography>
              </Box>
            ) : isMobile || isTablet ? (
              <Grid container spacing={2}>
                {reviewedApprovals.map((item) => (
                  <Grid item xs={12} sm={6} key={item.id}>
                    {renderApprovalCard(item)}
                  </Grid>
                ))}
              </Grid>
            ) : (
              renderApprovalTable(reviewedApprovals)
            )}
          </TabPanel>

          {approvals.length > 0 && (
            <Box sx={{ mt: 3, pt: 2, borderTop: 1, borderColor: 'divider' }}>
              <Typography variant="body2" color="text.secondary">
                Total: {approvals.length} items ({pendingApprovals.length} pending, {reviewedApprovals.length} reviewed)
              </Typography>
            </Box>
          )}
        </CardContent>
      </Card>

      {/* Review Dialog */}
      <Dialog open={openReviewDialog} onClose={() => setOpenReviewDialog(false)} maxWidth="sm" fullWidth fullScreen={isMobile}>
        <DialogTitle sx={{ fontWeight: 600, color: reviewAction === 'approve' ? '#2E7D32' : '#D32F2F' }}>
          {reviewAction === 'approve' ? (
            <>
              <ApproveIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
              Approve Data Entry
            </>
          ) : (
            <>
              <RejectIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
              Reject Data Entry
            </>
          )}
        </DialogTitle>
        <DialogContent>
          {reviewingItem && (
            <Box sx={{ mb: 2 }}>
              <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                <strong>KPI:</strong> {reviewingItem.kpi_name}
              </Typography>
              <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                <strong>Value:</strong> {reviewingItem.value} {reviewingItem.unit}
              </Typography>
              <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                <strong>Period:</strong> {dayjs(reviewingItem.period_start).format('MMM DD, YYYY')} to{' '}
                {dayjs(reviewingItem.period_end).format('MMM DD, YYYY')}
              </Typography>
              <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                <strong>Submitted by:</strong> {reviewingItem.submitted_by_name || 'Unknown'}
              </Typography>
            </Box>
          )}

          <TextField
            fullWidth
            label={reviewAction === 'approve' ? 'Review Notes (Optional)' : 'Rejection Reason (Required)'}
            value={reviewNotes}
            onChange={(e) => setReviewNotes(e.target.value)}
            multiline
            rows={4}
            required={reviewAction === 'reject'}
            placeholder={
              reviewAction === 'approve'
                ? 'Add any comments about this approval...'
                : 'Please explain why this entry is being rejected...'
            }
          />
        </DialogContent>
        <DialogActions sx={{ px: 3, pb: 3 }}>
          <Button onClick={() => setOpenReviewDialog(false)} color="inherit" disabled={processing !== null}>
            Cancel
          </Button>
          <Button
            onClick={handleReview}
            variant="contained"
            color={reviewAction === 'approve' ? 'success' : 'error'}
            disabled={processing !== null || (reviewAction === 'reject' && !reviewNotes.trim())}
            startIcon={reviewAction === 'approve' ? <ApproveIcon /> : <RejectIcon />}
          >
            {processing !== null ? 'Processing...' : reviewAction === 'approve' ? 'Approve' : 'Reject'}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
