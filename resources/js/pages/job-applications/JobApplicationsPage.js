import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { axiosClient } from '../../lib/axios';
import { Button } from '../../ui';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '../../ui/Card';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '../../ui/Table';
import { Badge } from '../../ui/Badge';
import { PageContainer } from '../../ui/PageContainer';
import { formatDate } from '../../utils/dates';
import { PlusCircle } from 'lucide-react';
const JobApplicationsPage = () => {
    const [applications, setApplications] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const navigate = useNavigate();
    const fetchApplications = async () => {
        try {
            setLoading(true);
            const response = await axiosClient.get('/api/job-applications');
            setApplications(Array.isArray(response.data) ? response.data : []);
            setError(null);
        }
        catch (err) {
            setError('Failed to load job applications');
            console.error(err);
        }
        finally {
            setLoading(false);
        }
    };
    useEffect(() => {
        fetchApplications();
    }, []);
    const getStatusBadge = (status) => {
        switch (status) {
            case 'applied':
                return _jsx(Badge, { variant: "secondary", children: "Applied" });
            case 'interviewing':
                return _jsx(Badge, { variant: "warning", children: "Interviewing" });
            case 'offered':
                return _jsx(Badge, { variant: "success", children: "Offered" });
            case 'rejected':
                return _jsx(Badge, { variant: "danger", children: "Rejected" });
            case 'withdrawn':
                return _jsx(Badge, { variant: "outline", children: "Withdrawn" });
            default:
                return _jsx(Badge, { variant: "default", children: status });
        }
    };
    return (_jsxs(PageContainer, { title: "Job Applications", subtitle: "Track and manage your job search applications", actions: _jsx(Button, { onClick: () => navigate('/job-applications/create'), variant: "filled", icon: _jsx(PlusCircle, { className: "h-4 w-4 mr-2" }), children: "Add Application" }), children: [error && (_jsx("div", { className: "mb-6", children: _jsx(Card, { variant: "elevated", children: _jsx(CardContent, { children: _jsx("div", { className: "bg-error/10 text-error p-4 rounded-lg", children: error }) }) }) })), _jsxs(Card, { variant: "elevated", children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "All Applications" }), _jsx(CardDescription, { children: "Track the status of all your job applications" })] }), _jsx(CardContent, { children: loading ? (_jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary" }) })) : applications.length === 0 ? (_jsxs("div", { className: "text-center py-8", children: [_jsx("p", { className: "text-on-surface-variant mb-4", children: "No job applications found" }), _jsx(Button, { onClick: () => navigate('/job-applications/create'), variant: "filled", children: "Add Your First Application" })] })) : (_jsx("div", { className: "overflow-x-auto", children: _jsxs(Table, { children: [_jsx(TableHeader, { children: _jsxs(TableRow, { children: [_jsx(TableHead, { children: "Company" }), _jsx(TableHead, { children: "Position" }), _jsx(TableHead, { children: "Applied On" }), _jsx(TableHead, { children: "Status" }), _jsx(TableHead, { children: "Actions" })] }) }), _jsx(TableBody, { children: applications.map((application) => (_jsxs(TableRow, { children: [_jsx(TableCell, { children: application.company_name }), _jsx(TableCell, { children: application.position }), _jsx(TableCell, { children: formatDate(application.application_date) }), _jsx(TableCell, { children: getStatusBadge(application.status) }), _jsx(TableCell, { children: _jsx(Link, { to: `/job-applications/${application.id}`, children: _jsx(Button, { variant: "outlined", size: "sm", children: "View" }) }) })] }, application.id))) })] }) })) })] }), applications.length > 0 && (_jsxs("div", { className: "mt-8 text-center", children: [_jsx("p", { className: "text-on-surface-variant mb-2", children: "Need help organizing your job search?" }), _jsx("p", { className: "text-on-surface mb-4", children: "Check out our tips for effective job application tracking." }), _jsx(Button, { variant: "outlined", onClick: () => window.open('/resources/job-search-tips', '_blank'), children: "View Job Search Tips" })] }))] }));
};
export default JobApplicationsPage;
