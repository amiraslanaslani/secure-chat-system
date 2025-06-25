const path = require('path');

module.exports = {
  entry: './front/main.ts',
  output: {
    filename: 'main.js',
    path: path.resolve(__dirname, 'project/statics/js'),
    clean: true,
  },
  resolve: {
    extensions: ['.ts', '.js'],
  },
  module: {
    rules: [
      {
        test: /\.ts$/,
        use: 'ts-loader',
        exclude: /node_modules/,
      },
      {
        enforce: 'pre',
        test: /\.js$/,
        loader: 'source-map-loader',
      },
    ],
  },
  devtool: 'source-map',
  mode: 'production',
}; 
