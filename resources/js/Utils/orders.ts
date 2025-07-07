export function getStatusColor(status: string): string {
  const colors = {
    pending: 'warning',
    processing: 'info',
    completed: 'success',
    cancelled: 'error',
  };
  return colors[status as keyof typeof colors] || 'grey';
}

export function getStatusIcon(status: string): string {
  const icons = {
    pending: 'mdi-clock-outline',
    processing: 'mdi-cog',
    completed: 'mdi-check-circle',
    cancelled: 'mdi-close-circle',
  };
  return icons[status as keyof typeof icons] || 'mdi-help-circle';
}

export function formatStatus(status: string): string {
  return status.charAt(0).toUpperCase() + status.slice(1);
}
