import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';
/**
 * Combines multiple class names into a single string.
 * Filters out falsy values (null, undefined, false, 0, "").
 *
 * @param classes - Class names to combine
 * @returns Combined class names as a string
 */
export function cn(...inputs) {
    return twMerge(clsx(inputs));
}
