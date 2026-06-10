import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

/**
 * Merge Tailwind class strings, resolving conflicts.
 * shadcn/ui standard utility — used by all UI primitives in `Components/ui/`.
 *
 * @param  {...any} inputs
 * @returns {string}
 */
export function cn(...inputs) {
    return twMerge(clsx(inputs));
}
