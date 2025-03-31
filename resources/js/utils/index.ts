import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'

/**
 * A utility function that merges multiple class names with Tailwind CSS
 * This helps avoid conflicts when combining utility classes
 */
export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}
