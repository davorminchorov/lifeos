import { jsx as _jsx, jsxs as _jsxs, Fragment as _Fragment } from "react/jsx-runtime";
import { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Button } from '../../ui';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '../../ui/Card';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '../../ui/Table';
import { Badge } from '../../ui/Badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../ui/Tabs';
import { PageContainer } from '../../ui/PageContainer';
import { formatDate } from '../../utils/dates';
import { ArrowLeft, Edit, Calendar, Building, Clock, MessageSquare, User, Mail, Link as LinkIcon, FileText, Briefcase } from 'lucide-react';
import InterviewModal from '../../components/job-applications/InterviewModal';
import OutcomeModal from '../../components/job-applications/OutcomeModal';
import { useJobApplicationDetail, useJobApplicationInterviews, useUpdateStatus } from '../../queries/jobApplicationQueries';
import { useToast } from '../../ui/Toast';
const JobApplicationDetailPage = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const { toast } = useToast();
    // React Query hooks
    const { data: application, isLoading, error, refetch } = useJobApplicationDetail(id);
    const { data: interviews = [] } = useJobApplicationInterviews(id);
    const updateStatusMutation = useUpdateStatus();
    const [showInterviewModal, setShowInterviewModal] = useState(false);
    const [showOutcomeModal, setShowOutcomeModal] = useState(false);
    const [activeTab, setActiveTab] = useState('details');
    const handleInterviewAdded = () => {
        setShowInterviewModal(false);
        refetch();
    };
    const handleOutcomeRecorded = () => {
        setShowOutcomeModal(false);
        refetch();
    };
    const handleEditApplication = () => {
        navigate(`/job-applications/${id}/edit`);
    };
    const updateStatus = (newStatus) => {
        if (!id)
            return;
        updateStatusMutation.mutate({ id, status: newStatus }, {
            onSuccess: () => {
                toast({
                    title: "Status updated",
                    description: `Application status changed to ${newStatus}`,
                    variant: "success",
                });
                refetch();
            },
            onError: (err) => {
                toast({
                    title: "Error",
                    description: "Failed to update status",
                    variant: "destructive",
                });
                console.error(err);
            }
        });
    };
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
    if (isLoading) {
        return (_jsx(PageContainer, { title: "Application Details", children: _jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary" }) }) }));
    }
    if (error || !application) {
        return (_jsx(PageContainer, { title: "Error", children: _jsx(Card, { variant: "elevated", children: _jsxs(CardContent, { children: [_jsx("div", { className: "bg-error/10 text-error p-4 rounded-lg mb-4", children: (error === null || error === void 0 ? void 0 : error.message) || 'Job application not found' }), _jsx(Button, { variant: "outlined", onClick: () => navigate('/job-applications'), children: "Back to Applications" })] }) }) }));
    }
    return (_jsxs(PageContainer, { title: application.position, subtitle: `${application.company_name} - Applied on ${formatDate(application.application_date)}`, actions: _jsxs("div", { className: "flex space-x-2", children: [_jsx(Button, { variant: "outlined", onClick: handleEditApplication, icon: _jsx(Edit, { className: "h-4 w-4 mr-2" }), children: "Edit Application" }), !['offered', 'rejected', 'withdrawn'].includes(application.status) && (_jsxs(_Fragment, { children: [_jsx(Button, { variant: "filled", onClick: () => setShowInterviewModal(true), icon: _jsx(Calendar, { className: "h-4 w-4 mr-2" }), children: "Schedule Interview" }), _jsx(Button, { variant: "outlined", onClick: () => setShowOutcomeModal(true), icon: _jsx(Briefcase, { className: "h-4 w-4 mr-2" }), children: "Record Outcome" })] }))] }), children: [_jsxs(Tabs, { value: activeTab, onValueChange: setActiveTab, className: "w-full mb-6", children: [_jsxs(TabsList, { className: "grid grid-cols-3", children: [_jsx(TabsTrigger, { value: "details", children: "Details" }), _jsxs(TabsTrigger, { value: "interviews", children: ["Interviews", application.interviews.length > 0 && (_jsx(Badge, { variant: "secondary", className: "ml-2", children: application.interviews.length }))] }), _jsx(TabsTrigger, { value: "outcome", disabled: !application.outcome, children: "Outcome" })] }), _jsx(TabsContent, { value: "details", className: "mt-4", children: _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-12 gap-6", children: [_jsx("div", { className: "md:col-span-8", children: _jsxs(Card, { variant: "elevated", children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Application Details" }), _jsx(CardDescription, { children: "Information about your job application" })] }), _jsxs(CardContent, { children: [_jsxs("dl", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [_jsxs("div", { className: "space-y-1", children: [_jsxs("dt", { className: "text-on-surface-variant text-sm flex items-center", children: [_jsx(Building, { className: "h-4 w-4 mr-1" }), "Company"] }), _jsx("dd", { className: "text-on-surface font-medium", children: application.company_name })] }), _jsxs("div", { className: "space-y-1", children: [_jsxs("dt", { className: "text-on-surface-variant text-sm flex items-center", children: [_jsx(User, { className: "h-4 w-4 mr-1" }), "Position"] }), _jsx("dd", { className: "text-on-surface font-medium", children: application.position })] }), _jsxs("div", { className: "space-y-1", children: [_jsxs("dt", { className: "text-on-surface-variant text-sm flex items-center", children: [_jsx(Calendar, { className: "h-4 w-4 mr-1" }), "Application Date"] }), _jsx("dd", { className: "text-on-surface font-medium", children: formatDate(application.application_date) })] }), _jsxs("div", { className: "space-y-1", children: [_jsx("dt", { className: "text-on-surface-variant text-sm", children: "Salary Range" }), _jsx("dd", { className: "text-on-surface font-medium", children: application.salary_range || 'Not specified' })] }), _jsxs("div", { className: "space-y-1", children: [_jsxs("dt", { className: "text-on-surface-variant text-sm flex items-center", children: [_jsx(User, { className: "h-4 w-4 mr-1" }), "Contact Person"] }), _jsx("dd", { className: "text-on-surface font-medium", children: application.contact_person || 'Not specified' })] }), _jsxs("div", { className: "space-y-1", children: [_jsxs("dt", { className: "text-on-surface-variant text-sm flex items-center", children: [_jsx(Mail, { className: "h-4 w-4 mr-1" }), "Contact Email"] }), _jsx("dd", { className: "text-on-surface font-medium", children: application.contact_email ? (_jsx("a", { href: `mailto:${application.contact_email}`, className: "text-primary hover:underline", children: application.contact_email })) : ('Not specified') })] }), application.application_url && (_jsxs("div", { className: "md:col-span-2 space-y-1", children: [_jsxs("dt", { className: "text-on-surface-variant text-sm flex items-center", children: [_jsx(LinkIcon, { className: "h-4 w-4 mr-1" }), "Application URL"] }), _jsx("dd", { className: "text-on-surface font-medium", children: _jsx("a", { href: application.application_url, target: "_blank", rel: "noopener noreferrer", className: "text-primary hover:underline", children: application.application_url }) })] }))] }), application.job_description && (_jsxs("div", { className: "mt-6", children: [_jsxs("h4", { className: "text-on-surface-variant text-sm flex items-center mb-2", children: [_jsx(FileText, { className: "h-4 w-4 mr-1" }), "Job Description"] }), _jsx("p", { className: "text-on-surface bg-surface-variant p-3 rounded-md whitespace-pre-line", children: application.job_description })] })), application.notes && (_jsxs("div", { className: "mt-6", children: [_jsxs("h4", { className: "text-on-surface-variant text-sm flex items-center mb-2", children: [_jsx(MessageSquare, { className: "h-4 w-4 mr-1" }), "Notes"] }), _jsx("p", { className: "text-on-surface bg-surface-variant p-3 rounded-md whitespace-pre-line", children: application.notes })] }))] })] }) }), _jsxs("div", { className: "md:col-span-4", children: [_jsxs(Card, { variant: "filled", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Status" }) }), _jsx(CardContent, { children: _jsxs("div", { className: "flex flex-col items-center py-4", children: [_jsx("div", { className: "bg-surface-variant w-16 h-16 rounded-full flex items-center justify-center mb-3", children: getStatusBadge(application.status) }), _jsx("h3", { className: "text-lg font-medium mb-2", children: application.status.charAt(0).toUpperCase() + application.status.slice(1) }), _jsxs("p", { className: "text-on-surface-variant text-center", children: [application.status === 'applied' && 'Your application has been submitted.', application.status === 'interviewing' && 'You are in the interview process.', application.status === 'offered' && 'Congratulations! You have received an offer.', application.status === 'rejected' && 'This application was not successful.', application.status === 'withdrawn' && 'You have withdrawn this application.'] })] }) })] }), _jsxs(Card, { variant: "outlined", className: "mt-6", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Quick Actions" }) }), _jsx(CardContent, { children: _jsx("div", { className: "space-y-3", children: !['offered', 'rejected', 'withdrawn'].includes(application.status) && (_jsxs(_Fragment, { children: [_jsx(Button, { variant: "filled", className: "w-full", onClick: () => setShowInterviewModal(true), icon: _jsx(Calendar, { className: "h-4 w-4 mr-2" }), children: "Schedule Interview" }), _jsx(Button, { variant: "outlined", className: "w-full", onClick: () => setShowOutcomeModal(true), children: "Record Outcome" })] })) }) })] })] })] }) }), _jsx(TabsContent, { value: "interviews", className: "mt-4", children: _jsxs(Card, { variant: "elevated", children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Interviews" }), _jsx(CardDescription, { children: "Track your interviews for this application" })] }), _jsxs(CardContent, { children: [application.interviews.length === 0 ? (_jsxs("div", { className: "text-center py-8", children: [_jsx(Clock, { className: "h-12 w-12 text-on-surface-variant mx-auto mb-2 opacity-50" }), _jsx("p", { className: "text-on-surface-variant mb-4", children: "No interviews scheduled yet" }), !['offered', 'rejected', 'withdrawn'].includes(application.status) && (_jsx(Button, { variant: "filled", onClick: () => setShowInterviewModal(true), icon: _jsx(Calendar, { className: "h-4 w-4 mr-2" }), children: "Schedule Interview" }))] })) : (_jsxs(Table, { children: [_jsx(TableHeader, { children: _jsxs(TableRow, { children: [_jsx(TableHead, { children: "Date & Time" }), _jsx(TableHead, { children: "Type" }), _jsx(TableHead, { children: "With" }), _jsx(TableHead, { children: "Location" }), _jsx(TableHead, { children: "Notes" })] }) }), _jsx(TableBody, { children: application.interviews.map((interview) => (_jsxs(TableRow, { children: [_jsxs(TableCell, { children: [_jsx("div", { className: "font-medium", children: formatDate(interview.interview_date) }), _jsx("div", { className: "text-on-surface-variant text-sm", children: interview.interview_time })] }), _jsx(TableCell, { children: interview.interview_type }), _jsx(TableCell, { children: interview.with_person || 'Not specified' }), _jsx(TableCell, { children: interview.location || 'Not specified' }), _jsx(TableCell, { children: interview.notes ? (_jsx("button", { onClick: () => alert(interview.notes), className: "text-primary hover:underline text-sm", children: "View Notes" })) : ('No notes') })] }, interview.id))) })] })), application.interviews.length > 0 && !['offered', 'rejected', 'withdrawn'].includes(application.status) && (_jsx("div", { className: "mt-6 flex justify-end", children: _jsx(Button, { variant: "filled", onClick: () => setShowInterviewModal(true), icon: _jsx(Calendar, { className: "h-4 w-4 mr-2" }), children: "Add Another Interview" }) }))] })] }) }), _jsx(TabsContent, { value: "outcome", className: "mt-4", children: application.outcome ? (_jsxs(Card, { variant: "elevated", children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Application Outcome" }), _jsx(CardDescription, { children: "Final result of your application" })] }), _jsxs(CardContent, { children: [_jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-6", children: [_jsxs("div", { children: [_jsx("h4", { className: "font-medium text-on-surface-variant mb-2", children: "Outcome" }), _jsxs("div", { className: "flex items-center", children: [getStatusBadge(application.status), _jsx("span", { className: "ml-2 font-medium", children: application.status.charAt(0).toUpperCase() + application.status.slice(1) })] })] }), _jsxs("div", { children: [_jsx("h4", { className: "font-medium text-on-surface-variant mb-2", children: "Date Recorded" }), _jsx("p", { children: formatDate(application.outcome.outcome_date) })] }), application.outcome.salary_offered && (_jsxs("div", { children: [_jsx("h4", { className: "font-medium text-on-surface-variant mb-2", children: "Salary Offered" }), _jsx("p", { children: application.outcome.salary_offered })] })), application.outcome.feedback && (_jsxs("div", { children: [_jsx("h4", { className: "font-medium text-on-surface-variant mb-2", children: "Feedback" }), _jsx("p", { children: application.outcome.feedback })] }))] }), application.outcome.notes && (_jsxs("div", { className: "mt-6", children: [_jsx("h4", { className: "font-medium text-on-surface-variant mb-2", children: "Additional Notes" }), _jsx("p", { className: "text-on-surface bg-surface-variant p-3 rounded-md whitespace-pre-line", children: application.outcome.notes })] }))] })] })) : (_jsx(Card, { variant: "elevated", children: _jsx(CardContent, { children: _jsxs("div", { className: "text-center py-8", children: [_jsx("p", { className: "text-on-surface-variant mb-4", children: "No outcome has been recorded yet" }), !['offered', 'rejected', 'withdrawn'].includes(application.status) && (_jsx(Button, { onClick: () => setShowOutcomeModal(true), variant: "filled", children: "Record Outcome" }))] }) }) })) })] }), _jsxs("div", { className: "flex justify-between mt-8", children: [_jsx(Button, { variant: "outlined", onClick: () => navigate('/job-applications'), icon: _jsx(ArrowLeft, { className: "h-4 w-4 mr-2" }), children: "Back to Applications" }), _jsx(Button, { variant: "text", className: "w-full", onClick: () => navigate(`/job-applications/${id}/edit`), children: "Edit Application" })] }), showInterviewModal && (_jsx(InterviewModal, { jobApplicationId: id, onClose: () => setShowInterviewModal(false), onSuccess: handleInterviewAdded })), showOutcomeModal && (_jsx(OutcomeModal, { jobApplicationId: id, onClose: () => setShowOutcomeModal(false), onSuccess: handleOutcomeRecorded }))] }));
};
export default JobApplicationDetailPage;
