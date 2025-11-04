const plugin = require('tailwindcss/plugin');
const themeName = __dirname.slice(__dirname.lastIndexOf('/') + 1);

module.exports = {
  content: [
    './layouts/**/*.htm',
    './pages/**/*.htm',
    './partials/**/*.htm',
    './partials/**/*.js',
    './js/**/*.js',
  ],
  theme: {
    extend: {
      transitionDuration: {
        '0': '0ms',
      },
      gridTemplateColumns: {
        'full': '100%',
        'social': 'repeat(auto-fill, 40px)',
        'payments': 'repeat(auto-fit, 56px)',
      },
      gridTemplateRows: {
        'auto-fr-auto': 'auto 1fr auto',
      },
      outline: {
        blue: ['1px dashed #1E40AF', '1px'],
      },
      backgroundSize: {
        '45': '45%',
        '65': '65%',
        '75': '75%',
        'info-icon': '12.5rem',
      },
      backgroundImage: {
        'check': `url('/themes/${themeName}/assets/images/check.svg')`,
        'check-black': `url('/themes/${themeName}/assets/images/check-black.svg')`,
        'feedback': `url('/themes/${themeName}/assets/images/union.svg')`,
        'arrow': `url('/themes/${themeName}/assets/images/marker.svg')`,
        'arrow-chevron': `url('/themes/${themeName}/assets/images/arrow-chevron.svg')`,
      },
      fontSize: {
        error: ['9rem', '1'],
      },
      maxWidth: {
        'screen': '100vw',
        'screen-3xl': '1920px',
        225: '900px',
      },
      padding: {
        54: '13.5rem',
        57: '14.25rem',
      },
      margin: {
        '-screen-1/2': '-50vw',
      },
      maxHeight: {
        160: '40rem',
      },
      minWidth: {
        18: '72px'
      },
      width: {
        90: '360px',
        174: '696px',
        23: '92px',
        225: '900px',
      },
      height: {
        'screen-3xl': '1920px',
        225: '900px',
        23: '92px',
        131: '524px'
      },
      spacing: {
        2.25: '9px',
        1.75: '7px',
        0.25: '1px',
        "inherit": 'inherit'
      },
      screens: {
        'ml': '840px',
        '2xl': '1280px',
      },
      aspectRatio: {
        '3/4': '3 / 4',
        '4/3': '4 / 3',
        '14/9': '14 / 9', // checkout
        '14/16': '14 / 16', // map
        '16/5': '16 / 5', // map
      },
      colors: {
        primary: {
          hover: "var(--primary-hover, #1D4ED8)", // blue-700
          focus: "var(--primary-focus, #1e3a8a)", // blue-900
          DEFAULT: "var(--primary, #1E40AF)", // blue-800
        },
        "on-primary": {
          DEFAULT: "var(--on-primary, #ffffff)", // white
          container: "var(--on-primary-container, #1e3a8a)", // blue-900
        },
        surface: {
          DEFAULT: "var(--surface, #f9fafb)", // gray-50
          dim: "var(--surface-dim, #d1d5db)", // gray-300
          container: "var(--surface-container, #f3f4f6)", // gray-100
        },
        "on-surface": {
          variant: "var(--on-surface-variant, #4b5563)", // gray-600
          DEFAULT: "var(--on-surface, #111827)", // gray-900
        },
        outline: {
          variant: "var(--outline-variant, #E5E7EB)", // gray-200
          DEFAULT: "var(--outline, #9CA3AF)", // gray-400
        },
      }
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('tailwindcss-pseudo-elements'),
    require('tailwindcss-content-visibility'),
    plugin(function ({ addUtilities }) {
      const newUtilities = {
        '.separator': {
          content: `url(/themes/${themeName}/assets/images/separator.svg)`,
        }
      };
      addUtilities(newUtilities, ['before'])
    }),
    plugin(({ addVariant, e }) => {
      addVariant('backdrop', ({ modifySelectors, separator }) => {
        modifySelectors(({ className }) => `.${e(`backdrop${separator}${className}`)}::backdrop`)
      })
    }),
  ]
};
