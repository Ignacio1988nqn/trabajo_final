import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    "./resources/**/*.js",
    "./resources/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
 darkMode: "class",
  theme: {
    extend: {
      colors: {
        surface: {
          DEFAULT: "#0b0f14", // (por si usas dark mode)
          light:   "#F6F7FB",
          card:    "#FFFFFF",
          border:  "#E6E8EE",
        },
        ink: {
          DEFAULT: "#0F172A", // slate-900
          soft:    "#334155", // slate-700
          mute:    "#64748B", // slate-500
        },
        admin: {
          primary:  "#3B82F6", // blue-500
          primary7: "#2563EB", // hover
          primary9: "#1D4ED8", // active
          accent:   "#06B6D4", // cyan-500
        },
        success:  "#10B981",
        warning:  "#F59E0B",
        danger:   "#EF4444",
        info:     "#0EA5E9",
      },
      boxShadow: {
        subtle: "0 1px 2px rgba(16,24,40,0.04)",
        card: "0 1px 2px rgba(0,0,0,0.05), 0 8px 24px rgba(16,24,40,0.06)",
      },
      borderRadius: {
        xl2: "1rem",
      },
      container: {
        center: true,
        padding: "1rem",
        screens: { lg: "1024px", xl: "1200px", "2xl": "1400px" },
      },
      fontFamily: {
        sans: ["Inter", "ui-sans-serif", "system-ui", "sans-serif"],
      },
    },
  },
    plugins: [forms],
};
