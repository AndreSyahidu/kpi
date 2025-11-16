import { Alert, AlertTitle, Button, Box } from '@mui/material';
import { getErrorMessage } from '../utils/errorMessages';

interface ErrorDisplayProps {
  error: any;
  onRetry?: () => void;
  onDismiss?: () => void;
}

/**
 * Reusable error display component with user-friendly messages
 */
export default function ErrorDisplay({ error, onRetry, onDismiss }: ErrorDisplayProps) {
  if (!error) return null;

  const { title, message, action, severity } = getErrorMessage(error);

  return (
    <Alert
      severity={severity}
      onClose={onDismiss}
      sx={{ mb: 2 }}
      action={
        onRetry && (
          <Button color="inherit" size="small" onClick={onRetry}>
            Retry
          </Button>
        )
      }
    >
      <AlertTitle>{title}</AlertTitle>
      <Box>{message}</Box>
      {action && (
        <Box sx={{ mt: 1, fontSize: '0.875rem', fontStyle: 'italic', opacity: 0.8 }}>
          💡 {action}
        </Box>
      )}
    </Alert>
  );
}

/**
 * Inline error message (for form fields)
 */
export function InlineError({ error }: { error?: any }) {
  if (!error) return null;

  const { message } = getErrorMessage(error);

  return (
    <Box sx={{ color: 'error.main', fontSize: '0.75rem', mt: 0.5 }}>
      {message}
    </Box>
  );
}
