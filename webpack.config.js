const path = require('path');
const fs = require('fs');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

// Helper: automatically find all blocks inside `src/blocks/*/index.js`
const blocksDir = path.resolve(__dirname, 'src/blocks');
const entries = {};

fs.readdirSync(blocksDir).forEach((blockName) => {
	const blockPath = path.join(blocksDir, blockName, 'index.js');
	if (fs.existsSync(blockPath)) {
		entries[`blocks/${blockName}/index`] = blockPath;
	}
});

module.exports = {
	...defaultConfig,
	entry: entries,
	output: {
		filename: '[name].js',
		path: path.resolve(__dirname, 'build'),
	},
	performance: {
		hints: false
	}
};
