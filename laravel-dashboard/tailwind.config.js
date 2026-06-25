import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",

    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Plus Jakarta Sans", ...defaultTheme.fontFamily.sans],
                display: ["Outfit", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: "rgb(var(--color-primary) / <alpha-value>)",
                    hover: "rgb(var(--color-primary-hover) / <alpha-value>)",
                    light: "rgb(var(--color-primary-light) / <alpha-value>)",
                },
                secondary: {
                    DEFAULT: "rgb(var(--color-secondary) / <alpha-value>)",
                },
                accent: {
                    DEFAULT: "rgb(var(--color-accent) / <alpha-value>)",
                },
                surface: {
                    DEFAULT: "rgb(var(--color-surface) / <alpha-value>)",
                    hover: "rgb(var(--color-surface-hover) / <alpha-value>)",
                },
                border: {
                    DEFAULT: "rgb(var(--color-border) / <alpha-value>)",
                },
                text: {
                    DEFAULT: "rgb(var(--color-text) / <alpha-value>)",
                    secondary: "rgb(var(--color-text-secondary) / <alpha-value>)",
                    tertiary: "rgb(var(--color-text-tertiary) / <alpha-value>)",
                },
                success: {
                    DEFAULT: "rgb(var(--color-success) / <alpha-value>)",
                },
                warning: {
                    DEFAULT: "rgb(var(--color-warning) / <alpha-value>)",
                },
                danger: {
                    DEFAULT: "rgb(var(--color-danger) / <alpha-value>)",
                },
            },
            backgroundColor: {
                page: "rgb(var(--color-background) / <alpha-value>)",
            },
        },
    },

    plugins: [forms],
};
