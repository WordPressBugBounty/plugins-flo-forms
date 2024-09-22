var gulp_argv = require("yargs").argv;

// this is true when running the 'gulp build' command
// we will use this to load the correct vue js version and define the mode
var running_build = gulp_argv["_"].includes("build");

var path = require("path");

//const BundleAnalyzerPlugin = require("webpack-bundle-analyzer").BundleAnalyzerPlugin;

module.exports = {
	//entry: './vue/',
	entry: {
		adminApp: "./vue/",
		adminFontsApp: "./vue/fonts/",
		app: "./public/",
	},
	mode: running_build ? "production" : "development",
	output: {
		path: path.resolve(__dirname, "./dist/js"),
		publicPath: "./dist/js",
		//filename: 'bundle.js'
		filename: "[name].min.js", // WE NEED both the file name and chunkFilename in order for Multiple chunks emit assets to independent filename
		chunkFilename: "[id].[chunkhash].js",
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				use: {
					loader: "babel-loader",
				},
				exclude: /node_modules/,
			},
			{
				test: /\.vue$/,
				loader: "vue-loader",
				options: {
					loaders: {
						scss: "vue-style-loader!css-loader!sass-loader",
						sass: "vue-style-loader!css-loader!sass-loader?indentedSyntax",
					},
				},
			},
		],
	},
	resolve: {
		extensions: [".js", ".vue", ".json"],
		alias: {
			vue$: running_build ? "vue/dist/vue.min.js" : "vue/dist/vue.common.js",
		},
	},
	performance: {
		hints: false,
	},
	devtool: running_build ? "" : "#eval-source-map",
	//plugins: [new BundleAnalyzerPlugin()],
};
