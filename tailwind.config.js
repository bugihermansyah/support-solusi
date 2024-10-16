import preset from './vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/awcodes/filament-quick-create/resources/**/*.blade.php',
        './Vendor/awcodes/filament-table-repeater/resources/**/*.blade.php',
        './vendor/guava/calendar/resources/**/*.blade.php',
        './vendor/kenepa/banner/resources/**/*.php',
    ],
}
