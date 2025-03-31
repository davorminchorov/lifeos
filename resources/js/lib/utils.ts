import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'

/**
 * Combines multiple class names with Tailwind CSS optimizations
 */
export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}
