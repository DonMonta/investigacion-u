const path = require('path');
const { defineConfig } = require('@vue/cli-service');
const webpack = require('webpack');

module.exports = defineConfig({
  transpileDependencies: true,

  publicPath: process.env.NODE_ENV === 'production' ? '/site/' : '/',
  outputDir: 'dist',
  assetsDir: 'assets',
  productionSourceMap: false,

  chainWebpack: config => {
    // ELIMINADO: Ya no necesitamos la regla 'ts' ni 'ts-loader'
    // Webpack por defecto ya sabe manejar .js y .vue
  },
  
  configureWebpack: {
    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'src/'),
      },
      // Simplificamos las extensiones (puedes quitar .ts y .tsx)
      extensions: ['.js', '.jsx', '.vue'], 
    },
    optimization: {
      minimize: true,
      splitChunks: {
        chunks: 'all',
      },
    },
    plugins: [
      new webpack.ProvidePlugin({
        $: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery',
      }),
      new webpack.DefinePlugin({
       __API_INVESTIGACION__: JSON.stringify(
          process.env.NODE_ENV === 'production'
            ? 'http://investigacionback.test/api'
            : 'http://investigacionback.test/api'
        ),
      }),
    ],
    output: {
      filename: 'assets/js/[name].[contenthash].js',
      chunkFilename: 'assets/js/[name].[contenthash].js',
    },
  },

  devServer: {
    proxy: {
      '/api': {
        target: 'http://investigacionback.test',
        changeOrigin: true,
        pathRewrite: { '^/api': '' },
      },
    },
  },
});