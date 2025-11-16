/**
 * Input Sanitization Utilities
 * Prevents XSS attacks by sanitizing user input
 */

/**
 * Sanitize HTML string - removes script tags and dangerous attributes
 * Use this before rendering ANY user-generated content
 */
export function sanitizeHtml(html: string): string {
  if (!html) return '';

  const div = document.createElement('div');
  div.textContent = html; // This escapes HTML
  return div.innerHTML;
}

/**
 * Sanitize string for display - escapes HTML entities
 */
export function escapeHtml(text: string): string {
  if (!text) return '';

  const map: Record<string, string> = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#x27;',
    '/': '&#x2F;',
  };

  return text.replace(/[&<>"'/]/g, (char) => map[char] || char);
}

/**
 * Sanitize URL - prevents javascript: and data: URLs
 */
export function sanitizeUrl(url: string): string {
  if (!url) return '';

  const trimmed = url.trim().toLowerCase();

  // Block dangerous protocols
  if (
    trimmed.startsWith('javascript:') ||
    trimmed.startsWith('data:') ||
    trimmed.startsWith('vbscript:')
  ) {
    return '#';
  }

  return url;
}

/**
 * Validate and sanitize email
 */
export function sanitizeEmail(email: string): string {
  if (!email) return '';

  // Basic email validation regex
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  const trimmed = email.trim();

  if (!emailRegex.test(trimmed)) {
    return '';
  }

  return trimmed.toLowerCase();
}

/**
 * Sanitize filename - remove path traversal attempts
 */
export function sanitizeFilename(filename: string): string {
  if (!filename) return '';

  // Remove path traversal attempts
  return filename
    .replace(/\.\./g, '')
    .replace(/[/\\]/g, '')
    .replace(/[<>:"|?*]/g, '')
    .trim();
}

/**
 * Sanitize number input
 */
export function sanitizeNumber(value: any): number | null {
  if (value === null || value === undefined || value === '') {
    return null;
  }

  const num = Number(value);

  if (isNaN(num) || !isFinite(num)) {
    return null;
  }

  return num;
}

/**
 * Sanitize JSON input - prevents injection attacks
 */
export function sanitizeJson(jsonString: string): any | null {
  if (!jsonString) return null;

  try {
    const parsed = JSON.parse(jsonString);

    // Recursively sanitize string values
    return sanitizeJsonObject(parsed);
  } catch (e) {
    console.error('Invalid JSON:', e);
    return null;
  }
}

function sanitizeJsonObject(obj: any): any {
  if (typeof obj === 'string') {
    return sanitizeHtml(obj);
  }

  if (Array.isArray(obj)) {
    return obj.map(sanitizeJsonObject);
  }

  if (obj && typeof obj === 'object') {
    const sanitized: any = {};
    for (const key in obj) {
      if (obj.hasOwnProperty(key)) {
        sanitized[key] = sanitizeJsonObject(obj[key]);
      }
    }
    return sanitized;
  }

  return obj;
}

/**
 * Sanitize search query - prevents SQL injection-like attacks
 */
export function sanitizeSearchQuery(query: string): string {
  if (!query) return '';

  return query
    .trim()
    .replace(/[<>]/g, '')
    .slice(0, 100); // Limit length
}

/**
 * Validate and sanitize phone number
 */
export function sanitizePhone(phone: string): string {
  if (!phone) return '';

  // Remove all non-numeric characters except + and -
  return phone.replace(/[^0-9+\-() ]/g, '').trim();
}

/**
 * Sanitize object - recursively sanitize all string values
 */
export function sanitizeObject<T extends Record<string, any>>(obj: T): T {
  const sanitized: any = {};

  for (const key in obj) {
    if (obj.hasOwnProperty(key)) {
      const value = obj[key];

      if (typeof value === 'string') {
        sanitized[key] = sanitizeHtml(value);
      } else if (Array.isArray(value)) {
        sanitized[key] = value.map((item) =>
          typeof item === 'string' ? sanitizeHtml(item) : item
        );
      } else if (value && typeof value === 'object') {
        sanitized[key] = sanitizeObject(value);
      } else {
        sanitized[key] = value;
      }
    }
  }

  return sanitized as T;
}

/**
 * Content Security Policy - Safe attributes for rendering
 */
export const CSP_SAFE_ATTRIBUTES = [
  'id',
  'class',
  'style',
  'title',
  'alt',
  'src',
  'href',
  'target',
  'rel',
];

/**
 * Validate that object only contains safe keys (no __proto__, constructor, etc.)
 */
export function hasSafeKeys(obj: any): boolean {
  if (!obj || typeof obj !== 'object') return true;

  const dangerousKeys = ['__proto__', 'constructor', 'prototype'];

  for (const key in obj) {
    if (dangerousKeys.includes(key.toLowerCase())) {
      return false;
    }

    if (typeof obj[key] === 'object' && obj[key] !== null) {
      if (!hasSafeKeys(obj[key])) {
        return false;
      }
    }
  }

  return true;
}
