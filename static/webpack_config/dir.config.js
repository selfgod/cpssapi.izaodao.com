var path = require('path');
var moduleExports = {};

moduleExports.root = path.resolve(__dirname, '../js/');
moduleExports.src = path.resolve(moduleExports.root, 'src/');
moduleExports.public = path.resolve(moduleExports.src, 'public');
moduleExports.modules = path.resolve(moduleExports.src, 'modules/');
moduleExports.lib = path.resolve(moduleExports.src, 'lib');
moduleExports.dist = path.resolve(moduleExports.root, 'dist');

module.exports = moduleExports;