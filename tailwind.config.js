import svgToDataUri from 'mini-svg-data-uri';
import { fontFamily } from 'tailwindcss/defaultTheme';
import plugin from 'tailwindcss/plugin';

export const colors = {
    primary: {
        50: '#D8EBFD',
        100: '#B1D6FB',
        200: '#64ADF7',
        300: '#1685F3',
        400: '#095CAF',
        500: '#053463',
        600: '#04294E',
        700: '#031F3A',
        800: '#021427',
        900: '#010A13',
        950: '#010A13',
    },
}

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './resources/**/*.{vue,ts,blade.php}',
        './storage/framework/views/*.php',
    ],
    theme: {
        fontFamily: {
            sans: ['Assistant', ...fontFamily.sans],
            serif: ['Nunito', ...fontFamily.serif],
        },
        extend: {
            colors,
        },
    },
    plugins: [
        plugin(function ({ addBase, theme }) {
            addBase({
                ['*']: {
                    letterSpacing: '1px',
                    WebkitTapHighlightColor: 'rgba(0, 0, 0, 0)'
                },
                ['*:focus']: {
                    outline: 'none'
                },
                ['input']: {
                    ['&::-webkit-autofill']: {
                        [[
                            '&',
                            '&:hover',
                            '&:focus',
                            '&:active',
                        ]]: {
                            transition: 'background-color 5000s ease-in-out 0s color 5000s ease-in-out 0s',
                        }
                    },
                    [[
                        '&::-webkit-search-decoration',
                        '&::-webkit-search-cancel-button',
                        '&::-webkit-search-results-button',
                        '&::-webkit-search-results-decoration',
                    ]]: {
                        [[
                            '&',
                            '&:hover',
                            '&:focus',
                            '&:active',
                        ]]: {
                            display: 'none',
                            appearance: 'none',
                            transition: 'appearance 5000s ease-in-out 0',
                        }
                    }
                },
                [[
                    `[type='text']`,
                    `[type='email']`,
                    `[type='url']`,
                    `[type='password']`,
                    `[type='number']`,
                    `[type='search']`,
                    `[type='tel']`,
                    'select',
                    'textarea',
                ]]: {
                    width: '100%',
                    appearance: 'none',
                    padding: theme('spacing.2'),
                    borderRadius: theme('borderRadius.DEFAULT'),
                    border: `1px solid ${theme('colors.gray.300')}`,

                    ['&:focus']: {
                        borderColor: theme('colors.gray.500'),
                    }
                },
                [[
                    'input',
                    'textarea',
                ]]: {
                    ['&::placeholder']: {
                        fontFamily: theme('fontFamily.sans'),
                        fontSize: theme('fontSize.sm'),
                    }
                },
                ['select:not([size])']: {
                    backgroundImage: `url("${svgToDataUri(
                        `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
							<path stroke="${theme('colors.gray.500')}" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 8l4 4 4-4"/>
						</svg>`,
                    )}")`,
                    backgroundPosition: `right ${theme('spacing.2')} center`,
                    backgroundRepeat: 'no-repeat',
                    backgroundSize: '1.5em 1.5em',
                    paddingRight: theme('spacing.10'),
                    printColorAdjust: 'exact',
                },
                [[
                    `[type='radio']`,
                    `[type='checkbox']`,
                ]]: {
                    minWidth: theme('spacing.4'),
                    minHeight: theme('spacing.4'),
                    accentColor: theme('colors.primary.600'),
                },
                [`[type='radio']:checked`]: {
                    backgroundImage: `url("${svgToDataUri(
                        `<svg viewBox="0 0 16 16" fill="white" xmlns="http://www.w3.org/2000/svg">
							<circle cx="8" cy="8" r="3"/>
						</svg>`,
                    )}")`,
                },
                [`[type='checkbox']:checked`]: {
                    backgroundImage: `url("${svgToDataUri(
                        `<svg viewBox="0 0 16 16" fill="white" xmlns="http://www.w3.org/2000/svg">
							<path d="M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z"/>
						</svg>`,
                    )}")`,
                },
                [[
                    `[type='button']`,
                    `[type='submit']`,
                    `[type='reset']`,
                    `[role='button']`,
                ]]: {
                    display: 'block',
                    padding: theme('spacing.2'),
                    borderRadius: theme('borderRadius.DEFAULT'),
                    border: `1px solid ${theme('colors.primary.500')}`,

                    ['&:hover']: {
                    },

                    ['&:disabled']: {
                        '--tw-bg-opacity': '0.8',
                    }
                },
                [`[type='file']`]: {
                    padding: 0,
                    borderRadius: theme('borderRadius.DEFAULT'),
                    border: `1px solid ${theme('colors.gray.300')}`,

                    ['&:hover']: {
                        outline: '1px auto inherit',
                    },

                    ['&::file-selector-button']: {
                        borderWidth: 0,
                        cursor: 'pointer',
                        marginInlineEnd: '1rem',
                        fontSize: theme('fontSize.medium'),
                        padding: theme('spacing[2.5]'),
                        backgroundColor: theme('colors.gray.200'),

                        ['&:hover']: {
                            outline: '1px auto inherit',
                            backgroundColor: theme('colors.gray.100'),
                        },
                    },
                },
            })
        }),
    ],
};
