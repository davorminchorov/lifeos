import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import axios from 'axios';
export const FileList = ({ entityId, entityType, onDelete, className = '', showEmpty = true, }) => {
    const [files, setFiles] = useState([]);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState(null);
    const fetchFiles = async () => {
        var _a, _b;
        setIsLoading(true);
        setError(null);
        try {
            const response = await axios.get('/api/files/entity', {
                params: {
                    entity_id: entityId,
                    entity_type: entityType,
                },
            });
            setFiles(response.data.data || []);
        }
        catch (err) {
            const errorMessage = ((_b = (_a = err.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.error) || 'Failed to load files';
            setError(errorMessage);
        }
        finally {
            setIsLoading(false);
        }
    };
    const handleDelete = async (fileId) => {
        var _a, _b;
        try {
            await axios.delete(`/api/files/${fileId}`);
            // Remove file from local state
            setFiles(files.filter(file => file.id !== fileId));
            // Notify parent if callback provided
            if (onDelete) {
                onDelete(fileId);
            }
        }
        catch (err) {
            const errorMessage = ((_b = (_a = err.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.error) || 'Failed to delete file';
            setError(errorMessage);
        }
    };
    // Fetch files on component mount
    useEffect(() => {
        fetchFiles();
    }, [entityId, entityType]);
    const formatFileSize = (bytes) => {
        if (bytes < 1024)
            return bytes + ' bytes';
        if (bytes < 1024 * 1024)
            return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    };
    if (isLoading) {
        return _jsx("div", { className: "text-sm text-gray-500", children: "Loading files..." });
    }
    if (error) {
        return _jsxs("div", { className: "text-sm text-red-500", children: ["Error: ", error] });
    }
    if (files.length === 0 && showEmpty) {
        return _jsx("div", { className: "text-sm text-gray-500", children: "No files attached" });
    }
    if (files.length === 0) {
        return null;
    }
    return (_jsx("div", { className: `file-list ${className}`, children: _jsx("ul", { className: "divide-y divide-gray-200", children: files.map(file => (_jsxs("li", { className: "py-3 flex justify-between items-center", children: [_jsxs("div", { className: "flex items-center space-x-3", children: [_jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-5 w-5 text-gray-400", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 2, d: "M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" }) }), _jsxs("div", { children: [_jsx("div", { className: "text-sm font-medium text-gray-900", children: file.name }), _jsx("div", { className: "text-xs text-gray-500", children: formatFileSize(file.size) })] })] }), _jsxs("div", { className: "flex space-x-2", children: [_jsx("a", { href: file.url, target: "_blank", rel: "noopener noreferrer", className: "text-sm text-blue-600 hover:text-blue-800", children: "View" }), _jsx("a", { href: file.download_url, className: "text-sm text-green-600 hover:text-green-800", children: "Download" }), _jsx("button", { onClick: () => handleDelete(file.id), className: "text-sm text-red-600 hover:text-red-800", children: "Delete" })] })] }, file.id))) }) }));
};
