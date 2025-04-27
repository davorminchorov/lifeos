import React, { ReactNode } from 'react';

interface PageTitleProps {
  title: string;
  description?: string;
  icon?: ReactNode;
}

export const PageTitle: React.FC<PageTitleProps> = ({
  title,
  description,
  icon
}) => {
  return (
    <div className="flex items-start">
      {icon && (
        <div className="mr-4 p-2 rounded-full bg-primary-container text-on-primary-container shadow-elevation-1">
          {icon}
        </div>
      )}
      <div>
        <h1 className="text-headline-large font-medium text-on-surface">{title}</h1>
        {description && (
          <p className="mt-1 text-body-large text-on-surface-variant">{description}</p>
        )}
      </div>
    </div>
  );
};

export default PageTitle;
