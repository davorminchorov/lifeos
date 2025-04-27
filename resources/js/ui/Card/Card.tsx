import React, { HTMLAttributes } from 'react';
import { cn } from '../../utils/cn';

export interface CardProps extends HTMLAttributes<HTMLDivElement> {
  variant?: 'default' | 'bordered' | 'elevated';
}

const Card: React.FC<CardProps> = ({
  children,
  className,
  variant = 'default',
  ...props
}) => {
  const variantClasses = {
    default: 'bg-white rounded-lg shadow',
    bordered: 'bg-white rounded-lg border border-gray-200',
    elevated: 'bg-white rounded-lg shadow-lg',
  };

  return (
    <div
      className={cn(variantClasses[variant], className)}
      {...props}
    >
      {children}
    </div>
  );
};

export default Card;
