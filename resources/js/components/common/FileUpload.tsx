import React, { useState, useCallback, useRef, ChangeEvent } from 'react';
import axios from 'axios';
import { Button } from '../../ui/Button';
import { useToast } from '../../ui/Toast';

interface FileUploadProps {
  entityId: string;
  entityType: string;
  onUploadSuccess?: (fileData: FileData) => void;
  onUploadError?: (error: Error) => void;
  maxSize?: number; // in MB
  allowedTypes?: string[]; // MIME types
  buttonText?: string;
  className?: string;
}

export interface FileData {
  id: string;
  url: string;
  download_url: string;
  name: string;
  mime_type: string;
  size: number;
  created_at?: string;
}

export const FileUpload: React.FC<FileUploadProps> = ({
  entityId,
  entityType,
  onUploadSuccess,
  onUploadError,
  maxSize = 10, // Default 10MB
  allowedTypes,
  buttonText = 'Upload File',
  className = '',
}) => {
  const [isUploading, setIsUploading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const { toast } = useToast();

  const validateFile = useCallback(
    (file: File): boolean => {
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
    },
    [maxSize, allowedTypes, toast]
  );

  const handleFileChange = useCallback(
    async (event: ChangeEvent<HTMLInputElement>) => {
      const files = event.target.files;
      if (!files || files.length === 0) return;

      const file = files[0];
      if (!validateFile(file)) return;

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
      } catch (err: any) {
        const errorMessage = err.response?.data?.error || 'Failed to upload file';
        setError(errorMessage);
        toast({
          title: "Upload Error",
          description: errorMessage,
          variant: "destructive",
        });
        if (onUploadError) {
          onUploadError(new Error(errorMessage));
        }
      } finally {
        setIsUploading(false);
        // Reset file input
        if (fileInputRef.current) {
          fileInputRef.current.value = '';
        }
      }
    },
    [entityId, entityType, validateFile, onUploadSuccess, onUploadError, toast]
  );

  const handleButtonClick = () => {
    if (fileInputRef.current) {
      fileInputRef.current.click();
    }
  };

  return (
    <div className={`file-upload ${className}`}>
      <input
        type="file"
        ref={fileInputRef}
        onChange={handleFileChange}
        className="hidden"
        disabled={isUploading}
      />
      <Button
        type="button"
        onClick={handleButtonClick}
        disabled={isUploading}
      >
        {isUploading ? 'Uploading...' : buttonText}
      </Button>

      {error && (
        <div className="mt-2 text-sm text-red-600">
          {error}
        </div>
      )}
    </div>
  );
};
