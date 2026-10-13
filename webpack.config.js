// Encore 7 is an ES module: require() returns its namespace, Encore is the default export.
var Encore = require('@symfony/webpack-encore').default;

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.scss) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/js/app.js')
    .addEntry('index', './assets/js/index.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()
    // Babel 8 picks JSX "development" output from BABEL_ENV/NODE_ENV, which
    // Encore doesn't set: without this, production builds called jsxDEV(),
    // missing from production React, and the recipe pop-up never mounted.
    .enableReactPreset((options) => {
        options.development = !Encore.isProduction();
    })

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.scss)
    .enableVersioning(Encore.isProduction())

    // No core-js polyfills: Babel 8 dropped the useBuiltIns/corejs options,
    // and every browser the site targets already has what assets/js uses.

    // enables Sass/SCSS support (Dart Sass)
    .enableSassLoader((options) => {
        options.sassOptions = {
            // Bootstrap 4's own deprecation warnings (slash division, global
            // functions...): not ours to fix, they would bury real warnings.
            quietDeps: true,
            // Our partials are still chained with @import: deprecated in Dart
            // Sass but supported until Sass 3.0; moving to @use means
            // dropping Bootstrap 4 first.
            silenceDeprecations: ['import'],
        };
    })

    // url("/img/...") in the stylesheets are files Apache serves from
    // public/ as they are: css-loader must leave them alone instead of
    // trying to bundle them (it fails, they are not in assets/).
    .configureCssLoader((options) => {
        options.url = { filter: (url) => !url.startsWith('/') };
    })

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/js/admin.js')
;

module.exports = Encore.getWebpackConfig();
