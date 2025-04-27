import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import { Button, Input, Select, Textarea, Card, CardHeader, CardTitle, CardContent, CardFooter } from '../../ui';
const ExampleForm = () => {
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        category: '',
        message: ''
    });
    const [errors, setErrors] = useState({});
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitted, setSubmitted] = useState(false);
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => (Object.assign(Object.assign({}, prev), { [name]: value })));
        // Clear error when user starts typing
        if (errors[name]) {
            setErrors((prev) => (Object.assign(Object.assign({}, prev), { [name]: undefined })));
        }
    };
    const validate = () => {
        const newErrors = {};
        if (!formData.name.trim()) {
            newErrors.name = 'Name is required';
        }
        if (!formData.email.trim()) {
            newErrors.email = 'Email is required';
        }
        else if (!/\S+@\S+\.\S+/.test(formData.email)) {
            newErrors.email = 'Email is invalid';
        }
        if (!formData.category) {
            newErrors.category = 'Please select a category';
        }
        if (!formData.message.trim()) {
            newErrors.message = 'Message is required';
        }
        else if (formData.message.length < 10) {
            newErrors.message = 'Message must be at least 10 characters';
        }
        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };
    const handleSubmit = (e) => {
        e.preventDefault();
        if (validate()) {
            setIsSubmitting(true);
            // Simulate API call
            setTimeout(() => {
                console.log('Form submitted:', formData);
                setIsSubmitting(false);
                setSubmitted(true);
                // Reset form after submission
                setTimeout(() => {
                    setFormData({
                        name: '',
                        email: '',
                        category: '',
                        message: ''
                    });
                    setSubmitted(false);
                }, 3000);
            }, 1000);
        }
    };
    return (_jsxs("div", { className: "max-w-2xl mx-auto", children: [_jsxs(Card, { children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Material Design Form Example" }) }), _jsx(CardContent, { children: submitted ? (_jsx("div", { className: "bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4", children: _jsx("p", { children: "Form submitted successfully!" }) })) : (_jsxs("form", { onSubmit: handleSubmit, className: "space-y-6", children: [_jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-6", children: [_jsx(Input, { label: "Name", name: "name", value: formData.name, onChange: handleChange, variant: "outlined", error: errors.name, helperText: "Enter your full name" }), _jsx(Input, { label: "Email", name: "email", type: "email", value: formData.email, onChange: handleChange, variant: "filled", error: errors.email })] }), _jsxs(Select, { label: "Category", name: "category", value: formData.category, onChange: handleChange, error: errors.category, children: [_jsx("option", { value: "", children: "Select a category" }), _jsx("option", { value: "feedback", children: "Feedback" }), _jsx("option", { value: "support", children: "Support" }), _jsx("option", { value: "feature", children: "Feature Request" }), _jsx("option", { value: "other", children: "Other" })] }), _jsx(Textarea, { label: "Message", name: "message", value: formData.message, onChange: handleChange, rows: 4, error: errors.message, helperText: "Minimum 10 characters" })] })) }), _jsx(CardFooter, { children: _jsxs("div", { className: "flex justify-end gap-4", children: [_jsx(Button, { variant: "text", onClick: () => setFormData({ name: '', email: '', category: '', message: '' }), children: "Reset" }), _jsx(Button, { variant: "contained", onClick: handleSubmit, isLoading: isSubmitting, disabled: isSubmitting || submitted, children: "Submit" })] }) })] }), _jsxs(Card, { className: "mt-8", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "UI Component Variants" }) }), _jsxs(CardContent, { children: [_jsx("h3", { className: "text-lg font-medium mb-4", children: "Buttons" }), _jsxs("div", { className: "flex flex-wrap gap-4 mb-8", children: [_jsx(Button, { variant: "contained", children: "Contained" }), _jsx(Button, { variant: "outlined", children: "Outlined" }), _jsx(Button, { variant: "text", children: "Text" }), _jsx(Button, { variant: "elevated", children: "Elevated" }), _jsx(Button, { variant: "tonal", children: "Tonal" })] }), _jsx("h3", { className: "text-lg font-medium mb-4", children: "Inputs" }), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-4 mb-8", children: [_jsx(Input, { label: "Outlined Input", variant: "outlined" }), _jsx(Input, { label: "Filled Input", variant: "filled" }), _jsx(Input, { label: "With Helper Text", helperText: "This is helper text", variant: "outlined" }), _jsx(Input, { label: "With Error", error: "This field has an error", variant: "outlined" })] }), _jsx("h3", { className: "text-lg font-medium mb-4", children: "Selects" }), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-4 mb-8", children: [_jsxs(Select, { label: "Outlined Select", variant: "outlined", children: [_jsx("option", { value: "1", children: "Option 1" }), _jsx("option", { value: "2", children: "Option 2" })] }), _jsxs(Select, { label: "Filled Select", variant: "filled", children: [_jsx("option", { value: "1", children: "Option 1" }), _jsx("option", { value: "2", children: "Option 2" })] })] }), _jsx("h3", { className: "text-lg font-medium mb-4", children: "Textareas" }), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [_jsx(Textarea, { label: "Outlined Textarea", variant: "outlined", rows: 3 }), _jsx(Textarea, { label: "Filled Textarea", variant: "filled", rows: 3 })] })] })] })] }));
};
export default ExampleForm;
