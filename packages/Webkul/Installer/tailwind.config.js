/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ["./src/Resources/**/*.blade.php", "./src/Resources/**/*.js"],

    theme: {
        container: {
            center: true,

            screens: {
                'xl': '1366px',
            },

            padding: {
                DEFAULT: '16px',
            },
        },

        screens: {
            sm: '525px',
            xl: '1366',
        },

        extend: {
            colors: {
                violet: {
                    50: '#f6f5f8',
                    100: '#edebf0',
                    200: '#d2cdda',
                    300: '#b6aec3',
                    400: '#817397',
                    500: '#4d396a',
                    600: '#312743',
                    700: '#231b30',
                    800: '#1b1426',
                    900: '#15101d',
                },
            },

            fontFamily: {
                inter: ['Inter'],
            }
        },
    },

    plugins: [],
}

