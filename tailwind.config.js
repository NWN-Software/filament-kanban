module.exports = {
    content: [
        './resources/views/**/*.blade.php',
        './resources/css/**/*.css',
    ],
    corePlugins: {
        // Disable preflight (CSS reset) to avoid conflicts with Filament
        preflight: false,
    },
    theme: {
        extend: {
            animation: {
                'pulse-twice': 'pulse 1s cubic-bezier(0, 0, 0.2, 1) 2',
            }
        }
    }
}