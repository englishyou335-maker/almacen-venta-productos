<?php
/**
 * Configuración visual compartida por las tres cabeceras (pública, admin y login).
 * Se incluye justo después de cargar el CDN de Tailwind para que el JIT la aplique.
 */
?>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    display: ['Outfit', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    script: ['"Great Vibes"', 'cursive'],
                },
                colors: {
                    ink: {
                        50: '#f4f7f8', 100: '#e5ecef', 200: '#c8d7dd', 300: '#a2b9c3',
                        400: '#7895a3', 500: '#5a7786', 600: '#455f6d', 700: '#394c58',
                        800: '#314049', 900: '#1c2a33', 950: '#111d24',
                    },
                    brand: {
                        50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7',
                        400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857',
                        800: '#065f46', 900: '#064e3b',
                    },
                    cream: '#f7f9f6',
                },
                boxShadow: {
                    soft: '0 12px 40px -20px rgba(16, 42, 35, 0.18)',
                    lift: '0 28px 55px -24px rgba(6, 78, 59, 0.40)',
                },
                borderRadius: {
                    '4xl': '2rem',
                },
                keyframes: {
                    'float-suave': {
                        '0%, 100%': { transform: 'translateY(0)' },
                        '50%': { transform: 'translateY(-12px)' },
                    },
                },
                animation: {
                    'float-suave': 'float-suave 7s ease-in-out infinite',
                },
            },
        },
    };
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
