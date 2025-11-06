declare global {
  interface Window {
    KPI_DASHBOARD_CONFIG: {
      apiUrl: string;
      siteUrl: string;
      baseUrl: string;
      nonce: string;
      version: string;
      companyName: string;
      companyLogo: string;
    };
  }
}

export const API_CONFIG = {
  baseURL: window.KPI_DASHBOARD_CONFIG?.apiUrl || '/wp-json/kpi/v1',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
  },
};

export const APP_CONFIG = {
  siteUrl: window.KPI_DASHBOARD_CONFIG?.siteUrl || '',
  baseUrl: window.KPI_DASHBOARD_CONFIG?.baseUrl || '/kpi',
  companyName: window.KPI_DASHBOARD_CONFIG?.companyName || 'MBD Corp',
  companyLogo: window.KPI_DASHBOARD_CONFIG?.companyLogo || '',
  version: window.KPI_DASHBOARD_CONFIG?.version || '1.0.0',
};
