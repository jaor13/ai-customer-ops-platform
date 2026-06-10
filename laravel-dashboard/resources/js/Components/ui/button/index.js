import { cva } from "class-variance-authority";

export { default as Button } from "./Button.vue";

/**
 * Button variants — shadcn-vue style API, mapped onto our brand tokens
 * (blue-600 primary, warm cream surface, rounded-full pills).
 */
export const buttonVariants = cva(
    "inline-flex items-center justify-center gap-2 whitespace-nowrap font-bold tracking-wide transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-page disabled:pointer-events-none disabled:opacity-50 [&_svg]:size-4 [&_svg]:shrink-0",
    {
        variants: {
            variant: {
                default:
                    "bg-blue-600 text-white shadow-md shadow-blue-500/10 hover:bg-blue-700 hover:shadow-blue-500/20",
                secondary:
                    "border border-border bg-white/80 backdrop-blur-sm text-text-secondary hover:bg-surface-hover hover:text-text dark:bg-surface/80",
                outline:
                    "border border-border bg-transparent text-text-secondary hover:bg-surface-hover hover:text-text",
                ghost: "text-text-secondary hover:bg-surface-hover hover:text-text",
                destructive:
                    "bg-danger text-white shadow-md shadow-red-500/10 hover:bg-red-600",
                link: "text-blue-600 underline-offset-4 hover:underline dark:text-blue-400",
            },
            size: {
                default: "h-9 px-4 py-2 text-xs",
                sm: "h-8 px-3 text-[11px]",
                lg: "h-11 px-6 text-sm",
                icon: "h-9 w-9",
            },
            shape: {
                pill: "rounded-full",
                rounded: "rounded-xl",
            },
        },
        defaultVariants: {
            variant: "default",
            size: "default",
            shape: "pill",
        },
    },
);
