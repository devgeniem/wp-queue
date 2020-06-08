/**
 * Webpack Build Configurations
 */

const webpack              = require( 'webpack' );
const ExtractTextPlugin    = require( 'extract-text-webpack-plugin' );
const path                 = require( 'path' ); // This resolves into the absolute path of the theme root.
const TerserPlugin         = require( 'terser-webpack-plugin' );

// Check for production mode.
const isProduction = process.env.NODE_ENV === 'production';

const postCss = {
    loader: 'postcss-loader',
    options: {
        sourceMap: true
    }
};

const cssLoader = {
    loader: 'css-loader',
    options: {
        sourceMap: true,
        minimize: true
    }
};

const sassLoader = {
    loader: 'sass-loader',
    options: {
        sourceMap: true
    }
};

const config = {
    devtool: 'source-map',
    entry: {
        public: './assets/scripts/public.js',
        admin: './assets/scripts/admin.js'
    },
    output: {
        path: path.resolve( './assets/dist' ),
        filename: '[name].js'
    },
    externals: {

        // Set jQuery to be an external resource.
        'jquery': 'jQuery'
    },
    plugins: [

        // Extract all css into one file.
        new ExtractTextPlugin( '[name].css', {
            allChunks: true
        }),

        // Provide jQuery instance for all modules.
        new webpack.ProvidePlugin({
            jQuery: 'jquery'
        })
    ],
    module: {
        rules: [
            {
                test: /\.js$/,
                include: [
                    path.resolve( __dirname, 'assets/admin/js' )
                ],
                use: {
                    loader: 'babel-loader',
                    options: {

                        // Do not use the .babelrc configuration file.
                        babelrc: false,

                        // The loader will cache the results of the loader in node_modules/.cache/babel-loader.
                        cacheDirectory: true,

                        // Enable latest JavaScript features.
                        presets: [ '@babel/preset-env' ],

                        // Enable dynamic imports.
                        plugins: [ '@babel/plugin-syntax-dynamic-import' ]
                    }
                }
            },
            {
                test: /\.css$/,
                use: ExtractTextPlugin.extract({
                    use: [ cssLoader, postCss ]
                })
            },
            {
                test: /\.scss$/,
                use: ExtractTextPlugin.extract({
                    use: [ cssLoader, postCss, sassLoader ]
                })
            },
            {
                test: /\.(woff(2)?|eot|ttf|otf)(\?[a-z0-9=\.]+)?$/,
                use: {
                    loader: 'url-loader?name=../fonts/[name].[ext]'
                }
            },
            {
                test: /\.(svg|gif|png|jpeg|jpg)(\?[a-z0-9=\.]+)?$/,
                use: {
                    loader: 'url-loader?name=../images/[name].[ext]'
                }
            }
        ]
    },
    watchOptions: {
        poll: 500
    }
};

// Check if minifyJs has been set true and minify.
if ( isProduction ) {
    // Optimize for production build.
    config.plugins.push = new TerserPlugin({
        cache: true,
        parallel: true,
        sourceMap: true,
        terserOptions: {
            output: {
                comments: false
            },
            compress: {
                warnings: false,
                drop_console: false // eslint-disable-line camelcase
            }
        }
    });

    // Delete distribution folder for production build.
    config.plugins.push( new CleanWebpackPlugin() );
}

module.exports = config;
