import preset from "../../../../vendor/filament/filament/tailwind.config.preset";

export default {
    presets: [preset],
    content: ["./resources/**/*.blade.php", "./vendor/filament/**/*.blade.php"],

    theme: {
        extend: {
            colors: {
                orange: {
                    150: "#FFF2EF",
                    250: "#FFD6CC",
                    350: "#FFBAA9",
                    450: "#FD9D85",
                    550: "#F27B5E",
                    650: "#D06348",
                    750: "#AE4D35",
                },
                gray: {
                    150: "#F5F5F5",
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
