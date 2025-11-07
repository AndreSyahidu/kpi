#!/bin/bash
# Remove unused imports
sed -i '/import.*useAuthStore.*from.*@\/store\/auth.store/d' assets/src/pages/approvals/ApprovalsPage.tsx
sed -i '/import.*useAuthStore.*from.*@\/store\/auth.store/d' assets/src/pages/data-entry/DataEntryPage.tsx
sed -i 's/CloudUpload as UploadIcon, Download as DownloadIcon, //' assets/src/pages/data-entry/DataEntryPage.tsx
echo "Remaining errors fixed!"
