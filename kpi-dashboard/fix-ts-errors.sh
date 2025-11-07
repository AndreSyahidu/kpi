#!/bin/bash

# Fix Approvals Page
sed -i 's/import {$/import {/' assets/src/pages/approvals/ApprovalsPage.tsx
sed -i '6s/  MenuItem,//' assets/src/pages/approvals/ApprovalsPage.tsx
sed -i '41,42d' assets/src/pages/approvals/ApprovalsPage.tsx

# Fix DataEntry Page
sed -i '5s/, Alert//' assets/src/pages/data-entry/DataEntryPage.tsx
sed -i '10s/, CloudUpload as UploadIcon, Download as DownloadIcon,/,/' assets/src/pages/data-entry/DataEntryPage.tsx  
sed -i '34,35d' assets/src/pages/data-entry/DataEntryPage.tsx

# Fix Settings Page
sed -i '5s/, FormControlLabel//' assets/src/pages/settings/SettingsPage.tsx
sed -i 's/user?.role ? roleColors\[user.role\]/user?.role ? (roleColors as any)[user.role]/g' assets/src/pages/settings/SettingsPage.tsx

# Fix Reports Page  
sed -i '3s/, Paper//' assets/src/pages/reports/ReportsPage.tsx
sed -i "s/dayjs().subtract(1, 'quarter').startOf('quarter')/dayjs().subtract(3, 'month').startOf('month')/g" assets/src/pages/reports/ReportsPage.tsx
sed -i "s/dayjs().subtract(1, 'quarter').endOf('quarter')/dayjs().subtract(1, 'month').endOf('month')/g" assets/src/pages/reports/ReportsPage.tsx

echo "TypeScript errors fixed!"
