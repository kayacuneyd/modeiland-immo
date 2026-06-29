/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './app/Views/**/*.php',
    './app/Modules/**/*.php',
    './app/Core/Controllers/**/*.php',
    './resources/**/*.php',
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
      {
        // modeiland — Estate module theme (navy/amber/cream palette from blueprint)
        modeiland: {
          'primary':          '#1B2A4A',  // navy
          'primary-content':  '#F6F1E7',  // cream
          'secondary':        '#44506B',  // slate
          'secondary-content':'#F6F1E7',
          'accent':           '#C7841A',  // amber
          'accent-content':   '#ffffff',
          'neutral':          '#44506B',
          'neutral-content':  '#F6F1E7',
          'base-100':         '#F6F1E7',  // cream
          'base-200':         '#EDE7DA',
          'base-300':         '#DDD7CA',
          'base-content':     '#1B2A4A',
          'info':             '#3B82F6',
          'info-content':     '#ffffff',
          'success':          '#2F6B4F',  // success green
          'success-content':  '#ffffff',
          'warning':          '#C7841A',
          'warning-content':  '#1B2A4A',
          'error':            '#9B2C2C',  // danger
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
