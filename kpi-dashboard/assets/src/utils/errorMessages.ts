/**
 * User-Friendly Error Messages
 * Converts technical error codes to actionable user messages
 */

export interface ErrorMessage {
  title: string;
  message: string;
  action?: string;
  severity: 'error' | 'warning' | 'info';
}

/**
 * Get user-friendly error message from API error response
 */
export function getErrorMessage(error: any): ErrorMessage {
  // Network errors
  if (!error.response) {
    return {
      title: 'Connection Error',
      message: 'Unable to connect to the server. Please check your internet connection.',
      action: 'Try again in a moment',
      severity: 'error',
    };
  }

  const status = error.response?.status;
  const code = error.response?.data?.code;
  const message = error.response?.data?.message;

  // Rate limiting
  if (status === 429 || code === 'too_many_attempts') {
    const retryAfter = error.response?.data?.retry_after;
    const minutes = retryAfter ? Math.ceil(retryAfter / 60) : 15;

    return {
      title: 'Too Many Attempts',
      message: `You've made too many attempts. Please wait ${minutes} minutes before trying again.`,
      action: `Try again in ${minutes} minutes`,
      severity: 'warning',
    };
  }

  // Authentication errors
  if (status === 401 || code === 'not_authenticated') {
    return {
      title: 'Session Expired',
      message: 'Your session has expired. Please log in again to continue.',
      action: 'Go to login page',
      severity: 'warning',
    };
  }

  if (code === 'invalid_credentials') {
    return {
      title: 'Invalid Credentials',
      message: 'The username or password you entered is incorrect.',
      action: 'Double-check your credentials and try again',
      severity: 'error',
    };
  }

  // Permission errors
  if (status === 403 || code === 'forbidden' || code === 'permission_denied') {
    return {
      title: 'Access Denied',
      message: message || 'You don\'t have permission to perform this action.',
      action: 'Contact your administrator if you believe this is an error',
      severity: 'warning',
    };
  }

  // Not found errors
  if (status === 404 || code === 'not_found') {
    return {
      title: 'Not Found',
      message: message || 'The requested resource could not be found.',
      action: 'Check the URL or go back to the previous page',
      severity: 'error',
    };
  }

  // Validation errors
  if (status === 422 || code === 'validation_error') {
    return {
      title: 'Invalid Input',
      message: message || 'Please check your input and try again.',
      action: 'Review the highlighted fields',
      severity: 'warning',
    };
  }

  // Server errors
  if (status >= 500) {
    return {
      title: 'Server Error',
      message: 'Something went wrong on our end. Our team has been notified.',
      action: 'Try again in a few minutes',
      severity: 'error',
    };
  }

  // Specific error codes
  const errorMap: Record<string, ErrorMessage> = {
    // Authentication
    'account_locked': {
      title: 'Account Locked',
      message: 'Your account has been temporarily locked due to too many failed login attempts.',
      action: 'Try again in 15 minutes or contact support',
      severity: 'error',
    },
    'weak_password': {
      title: 'Weak Password',
      message: 'Your password must be at least 8 characters and include uppercase, lowercase, and numbers.',
      action: 'Choose a stronger password',
      severity: 'warning',
    },
    'password_mismatch': {
      title: 'Passwords Don\'t Match',
      message: 'The passwords you entered don\'t match.',
      action: 'Make sure both password fields are identical',
      severity: 'warning',
    },

    // Data validation
    'invalid_date_range': {
      title: 'Invalid Date Range',
      message: 'The start date must be before the end date.',
      action: 'Adjust your date selection',
      severity: 'warning',
    },
    'invalid_start_date': {
      title: 'Invalid Start Date',
      message: 'The start date format is incorrect.',
      action: 'Use the date picker to select a valid date',
      severity: 'warning',
    },
    'invalid_end_date': {
      title: 'Invalid End Date',
      message: 'The end date format is incorrect.',
      action: 'Use the date picker to select a valid date',
      severity: 'warning',
    },
    'invalid_future_date': {
      title: 'Date Too Far in Future',
      message: 'You cannot enter data more than 1 year in the future.',
      action: 'Select a date within the next year',
      severity: 'warning',
    },

    // KPI errors
    'kpi_already_assigned': {
      title: 'Already Assigned',
      message: 'This KPI is already assigned to the selected department or position.',
      action: 'Choose a different target or KPI',
      severity: 'warning',
    },
    'missing_target': {
      title: 'Missing Target',
      message: 'You must set a target value for this KPI.',
      action: 'Enter a target value before saving',
      severity: 'warning',
    },
    'invalid_formula': {
      title: 'Invalid Formula',
      message: 'The calculation formula contains errors.',
      action: 'Check the formula syntax and try again',
      severity: 'error',
    },

    // File upload
    'file_too_large': {
      title: 'File Too Large',
      message: 'The file you selected exceeds the maximum size of 10MB.',
      action: 'Choose a smaller file',
      severity: 'warning',
    },
    'invalid_file_type': {
      title: 'Invalid File Type',
      message: 'This file type is not supported.',
      action: 'Please upload a PDF, PNG, JPG, or XLSX file',
      severity: 'warning',
    },

    // Approval workflow
    'already_approved': {
      title: 'Already Approved',
      message: 'This entry has already been approved.',
      action: 'Refresh the page to see the latest status',
      severity: 'info',
    },
    'already_rejected': {
      title: 'Already Rejected',
      message: 'This entry has already been rejected.',
      action: 'Refresh the page to see the latest status',
      severity: 'info',
    },
    'notes_required': {
      title: 'Notes Required',
      message: 'You must provide a reason when rejecting an entry.',
      action: 'Add review notes before submitting',
      severity: 'warning',
    },

    // Data entry
    'duplicate_entry': {
      title: 'Duplicate Entry',
      message: 'An entry for this KPI and period already exists.',
      action: 'Edit the existing entry instead of creating a new one',
      severity: 'warning',
    },
    'invalid_value': {
      title: 'Invalid Value',
      message: 'The value you entered is not in the acceptable range.',
      action: 'Enter a value between the min and max targets',
      severity: 'warning',
    },
  };

  if (code && errorMap[code]) {
    return errorMap[code];
  }

  // Default error message
  return {
    title: 'Error',
    message: message || 'An unexpected error occurred.',
    action: 'Try again or contact support if the problem persists',
    severity: 'error',
  };
}

/**
 * Get success message for common operations
 */
export function getSuccessMessage(operation: string, entity?: string): string {
  const messages: Record<string, string> = {
    'create': `${entity || 'Item'} created successfully`,
    'update': `${entity || 'Item'} updated successfully`,
    'delete': `${entity || 'Item'} deleted successfully`,
    'approve': `${entity || 'Entry'} approved successfully`,
    'reject': `${entity || 'Entry'} rejected successfully`,
    'assign': `Assignment completed successfully`,
    'upload': `File uploaded successfully`,
    'export': `Data exported successfully`,
    'import': `Data imported successfully`,
    'save': `Changes saved successfully`,
  };

  return messages[operation] || 'Operation completed successfully';
}

/**
 * Format field errors from validation response
 */
export function formatFieldErrors(errors: Record<string, string[]>): string {
  const fieldNames: Record<string, string> = {
    'username': 'Username',
    'email': 'Email',
    'password': 'Password',
    'full_name': 'Full Name',
    'department_id': 'Department',
    'position_id': 'Position',
    'kpi_id': 'KPI',
    'period_start': 'Start Date',
    'period_end': 'End Date',
    'value': 'Value',
    'target': 'Target',
  };

  const messages: string[] = [];

  for (const [field, fieldErrors] of Object.entries(errors)) {
    const friendlyName = fieldNames[field] || field.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
    messages.push(`${friendlyName}: ${fieldErrors.join(', ')}`);
  }

  return messages.join('\n');
}
