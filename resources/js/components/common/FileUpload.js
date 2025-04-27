import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useCallback, useRef } from 'react';
import axios from 'axios';
import { Button } from '../../ui/Button';
import { useToast } from '../../ui/Toast';
export const FileUpload = ({ entityId, entityType, onUploadSuccess, onUploadError, maxSize = 10, // Default 10MB
allowedTypes, buttonText = 'Upload File', className = '', }) => {
    const [isUploading, setIsUploading] = useState(false);
    const [error, setError] = useState(null);
    const fileInputRef = useRef(null);
    const { toast } = useToast();
    const validateFile = useCallback((file) => {
        // Check file size
        if (file.size > maxSize * 1024 * 1024) {
            const errorMsg = `File size exceeds the maximum allowed size of ${maxSize}MB`;
            setError(errorMsg);
            toast({
                title: "File Size Error",
                description: errorMsg,
                variant: "destructive",
            });
            return false;
        }
        // Check file type if specified
        if (allowedTypes && allowedTypes.length > 0) {
            if (!allowedTypes.includes(file.type)) {
                const errorMsg = `File type not allowed. Accepted types: ${allowedTypes.join(', ')}`;
                setError(errorMsg);
                toast({
                    title: "File Type Error",
                    description: errorMsg,
                    variant: "destructive",
                });
                return false;
            }
        }
        return true;
    }, [maxSize, allowedTypes, toast]);
    const handleFileChange = useCallback(async (event) => {
        var _a, _b;
        const files = event.target.files;
        if (!files || files.length === 0)
            return;
        const file = files[0];
        if (!validateFile(file))
            return;
        // Clear previous errors
        setError(null);
        setIsUploading(true);
        try {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('entity_id', entityId);
            formData.append('entity_type', entityType);
            const response = await axios.post('/api/files/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            toast({
                title: "Success",
                description: "File uploaded successfully",
                variant: "success",
            });
            if (onUploadSuccess) {
                onUploadSuccess(response.data);
            }
        }
        catch (err) {
            const errorMessage = ((_b = (_a = err.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.error) || 'Failed to upload file';
            setError(errorMessage);
            toast({
                title: "Upload Error",
                description: errorMessage,
                variant: "destructive",
            });
            if (onUploadError) {
                onUploadError(new Error(errorMessage));
            }
        }
        finally {
            setIsUploading(false);
            // Reset file input
            if (fileInputRef.current) {
                fileInputRef.current.value = '';
            }
        }
    }, [entityId, entityType, validateFile, onUploadSuccess, onUploadError, toast]);
    const handleButtonClick = () => {
        if (fileInputRef.current) {
            fileInputRef.current.click();
        }
    };
    return (_jsxs("div", { className: `file-upload ${className}`, children: [_jsx("input", { type: "file", ref: fileInputRef, onChange: handleFileChange, className: "hidden", disabled: isUploading }), _jsx(Button, { type: "button", onClick: handleButtonClick, disabled: isUploading, children: isUploading ? 'Uploading...' : buttonText }), error && (_jsx("div", { className: "mt-2 text-sm text-red-600", children: error }))] }));
};
