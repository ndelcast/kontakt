export function useFormatters() {
    const formatCurrency = (value) => {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR',
        }).format(value ?? 0);
    };

    const formatDate = (date) => {
        if (!date) return '';
        return new Date(date).toLocaleDateString('fr-FR');
    };

    const formatDateTime = (date) => {
        if (!date) return '';
        return new Date(date).toLocaleDateString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const priorityColor = (priority) => {
        return { 2: 'danger', 1: 'warn', 0: 'secondary' }[priority] ?? 'secondary';
    };

    const priorityLabel = (priority) => {
        return { 2: 'Urgent', 1: 'High', 0: 'Normal' }[priority] ?? 'Normal';
    };

    const taskTypeColor = (type) => {
        return {
            call: 'success',
            email: 'info',
            meeting: 'warn',
            follow_up: 'primary',
            note: 'secondary',
            other: 'secondary',
        }[type] ?? 'secondary';
    };

    return { formatCurrency, formatDate, formatDateTime, priorityColor, priorityLabel, taskTypeColor };
}
