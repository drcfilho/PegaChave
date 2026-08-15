/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/Views/**/*.blade.php",
    "./src/Views/*.blade.php",
    "./api/**/*.js"
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: 'var(--primary, #0ea5e9)',
        secondary: 'var(--sidebar-bg, #0f172a)',
        brand: {
          400: 'var(--theme-cor-primaria, #16a34a)',
          500: 'var(--theme-cor-primaria, #15803d)',
          600: 'var(--theme-cor-primaria, #166534)',
          800: 'var(--theme-cor-secundaria, #14532d)'
        }
      },
      keyframes: {
        slideIn: { from: { transform: 'translateX(120%)' }, to: { transform: 'translateX(0)' } },
        fadeIn: { from: { opacity: '0' }, to: { opacity: '1' } }
      },
      animation: {
        'slide-in': 'slideIn 0.3s ease-out forwards',
        'fade-in': 'fadeIn 0.3s ease-out forwards'
      }
    }
  },
  plugins: [],
}
