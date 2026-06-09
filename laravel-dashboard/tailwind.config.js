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
                    DEFAULT: "var(--color-primary)",
                    hover: "var(--color-primary-hover)",
                    light: "var(--color-primary-light)",
                },
                secondary: {
                    DEFAULT: "var(--color-secondary)",
                },
                accent: {
                    DEFAULT: "var(--color-accent)",
                },
                surface: {
                    DEFAULT: "var(--color-surface)",
                    hover: "var(--color-surface-hover)",
                },
                border: {
                    DEFAULT: "var(--color-border)",
                },
                text: {
                    DEFAULT: "var(--color-text)",
                    secondary: "var(--color-text-secondary)",
                    tertiary: "var(--color-text-tertiary)",
                },
                success: {
                    DEFAULT: "var(--color-success)",
                },
                warning: {
                    DEFAULT: "var(--color-warning)",
                },
                danger: {
                    DEFAULT: "var(--color-danger)",
                },
            },
            backgroundColor: {
                page: "var(--color-background)",
            },
        },
    },

    plugins: [forms],
};
