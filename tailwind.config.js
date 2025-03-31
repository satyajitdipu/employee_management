import preset from '../cranberry-cookie/vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                blue: {
                    50: "#32a0bf",
                    100: "#2896b5",
                    200: "#1e8cab",
                    300: "#1482a1",
                    400: "#0a7897",
                    500: "#006e8d",
                    600: "#006483",
                    700: "#005a79",
                    800: "#00506f",
                    900: "#004665",
                },
                orange: {
                    150: "#FFF2EF",
                    250:"#FFD6CC",
                    350: "#FFBAA9",
                    450: "#FD9D85",
                    550: "#F27B5E",
                    650: "#D06348",
                    750: "#AE4D35",
                },
                gray: {
                    20: "#F1F1F1",
                    30: "#F5F5F5",
                    750: "#565656",
                },
              
            },
            screens: {
                xxs: "325px",
                // => @media (min-width: 325px) { ... }

                xs: "415px",
                // => @media (min-width: 415px) { ... }

                sm: "640px",
                // => @media (min-width: 640px) { ... }

                md: "768px",
                // => @media (min-width: 768px) { ... }

                lg: "1024px",
                // => @media (min-width: 1024px) { ... }

                xl: "1280px",
                // => @media (min-width: 1280px) { ... }

                xxl: "1536px",
                // => @media (min-width: 1536px) { ... }
            },
        },
    },
};


