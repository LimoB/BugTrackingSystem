/** @type {import('tailwindcss').Config} */
module.exports = {
  // 1. Enable 'class' strategy so your JS toggle works
  darkMode: 'class', 
  
  content: [
    "./index.php",
    "./public/**/*.php",
    "./includes/**/*.php", // Added this to catch headers/footers
    "./assets/js/**/*.js", // Added this to catch dynamic JS classes
    "./src/**/*.{html,js,php}",
  ],
  
  theme: {
    extend: {
      colors: {
        // Custom "Dim" colors for a high-end dark mode look
        brand: {
          50: '#ecfdf5',
          100: '#d1fae5',
          600: '#059669', // Your Emerald Green
          700: '#047857',
        },
        darkbg: {
          900: '#0f172a', // Sidebar/Card Dim
          950: '#020617', // Main Background (Near Black)
        }
      },
      fontFamily: {
        // Matching the 'Plus Jakarta Sans' from your HTML
        sans: ['Plus Jakarta Sans', 'sans-serif'],
      },
    },
  },
  plugins: [],
}