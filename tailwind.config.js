/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './app/Views/**/*.php',
    './app/Modules/**/*.php',
    './app/Core/Controllers/**/*.php',
  ],
  plugins: [require('daisyui')],
  daisyui: {
    themes: [
      {
        cekirdekcms: {
          'primary':          '#1B2D42',
          'primary-content':  '#F5F0E8',
          'secondary':        '#2E4A6B',
          'secondary-content':'#F5F0E8',
          'accent':           '#E07B39',
          'accent-content':   '#ffffff',
          'neutral':          '#374151',
          'neutral-content':  '#F5F0E8',
          'base-100':         '#F5F0E8',
          'base-200':         '#EDE8DF',
          'base-300':         '#D9D3C8',
          'base-content':     '#1B2D42',
          'info':             '#3B82F6',
          'info-content':     '#ffffff',
          'success':          '#22C55E',
          'success-content':  '#ffffff',
          'warning':          '#F59E0B',
          'warning-content':  '#1B2D42',
          'error':            '#EF4444',
          'error-content':    '#ffffff',
        },
      },
    ],
    darkTheme: false,
    base:      true,
    styled:    true,
    utils:     true,
    logs:      false,
  },
};
