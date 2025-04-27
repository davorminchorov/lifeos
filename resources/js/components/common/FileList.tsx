import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { FileData } from './FileUpload';

interface FileListProps {
  entityId: string;
  entityType: string;
  onDelete?: (fileId: string) => void;
  className?: string;
  showEmpty?: boolean;
}

export const FileList: React.FC<FileListProps> = ({
  entityId,
  entityType,
  onDelete,
  className = '',
  showEmpty = true,
}) => {
  const [files, setFiles] = useState<FileData[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const fetchFiles = async () => {
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
    } catch (err: any) {
      const errorMessage = err.response?.data?.error || 'Failed to load files';
      setError(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  const handleDelete = async (fileId: string) => {
    try {
      await axios.delete(`/api/files/${fileId}`);

      // Remove file from local state
      setFiles(files.filter(file => file.id !== fileId));

      // Notify parent if callback provided
      if (onDelete) {
        onDelete(fileId);
      }
    } catch (err: any) {
      const errorMessage = err.response?.data?.error || 'Failed to delete file';
      setError(errorMessage);
    }
  };

  // Fetch files on component mount
  useEffect(() => {
    fetchFiles();
  }, [entityId, entityType]);

  const formatFileSize = (bytes: number): string => {
    if (bytes < 1024) return bytes + ' bytes';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
  };

  if (isLoading) {
    return <div className="text-sm text-gray-500">Loading files...</div>;
  }

  if (error) {
    return <div className="text-sm text-red-500">Error: {error}</div>;
  }

  if (files.length === 0 && showEmpty) {
    return <div className="text-sm text-gray-500">No files attached</div>;
  }

  if (files.length === 0) {
    return null;
  }

  return (
    <div className={`file-list ${className}`}>
      <ul className="divide-y divide-gray-200">
        {files.map(file => (
          <li key={file.id} className="py-3 flex justify-between items-center">
            <div className="flex items-center space-x-3">
              <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <div>
                <div className="text-sm font-medium text-gray-900">
                  {file.name}
                </div>
                <div className="text-xs text-gray-500">
                  {formatFileSize(file.size)}
                </div>
              </div>
            </div>
            <div className="flex space-x-2">
              <a
                href={file.url}
                target="_blank"
                rel="noopener noreferrer"
                className="text-sm text-blue-600 hover:text-blue-800"
              >
                View
              </a>
              <a
                href={file.download_url}
                className="text-sm text-green-600 hover:text-green-800"
              >
                Download
              </a>
              <button
                onClick={() => handleDelete(file.id)}
                className="text-sm text-red-600 hover:text-red-800"
              >
                Delete
              </button>
            </div>
          </li>
        ))}
      </ul>
    </div>
  );
};
