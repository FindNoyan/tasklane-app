/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        paper: "#FAFAF7",
        surface: "#FFFFFF",
        ink: "#23261F",
        muted: "#767A70",
        line: "#E7E5DC",
        teal: "#2F8F82",
        ochre: "#C98A3B",
      },
      fontFamily: {
        display: ["Fraunces", "serif"],
        body: ["Inter", "sans-serif"],
      },
    },
  },
  plugins: [],
};
