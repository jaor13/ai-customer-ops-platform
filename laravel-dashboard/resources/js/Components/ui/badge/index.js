import { cva } from "class-variance-authority";

export { default as Badge } from "./Badge.vue";

export const badgeVariants = cva(
    "inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest transition-colors",
    {
        variants: {
            variant: {
                default:
                    "border-primary/20 bg-primary-light text-primary",
                secondary: "border-border bg-surface-hover text-text-secondary",
                success:
                    "border-success/20 bg-success/10 text-success",
                warning:
                    "border-warning/20 bg-warning/10 text-warning",
                danger: "border-danger/20 bg-danger/10 text-danger",
                outline: "border-border bg-transparent text-text-secondary",
                hot: "border-danger/20 bg-danger/10 text-danger",
                warm: "border-warning/20 bg-warning/10 text-warning",
                cold: "border-secondary/20 bg-secondary/10 text-secondary",
            },
        },
        defaultVariants: {
            variant: "default",
        },
    },
);
