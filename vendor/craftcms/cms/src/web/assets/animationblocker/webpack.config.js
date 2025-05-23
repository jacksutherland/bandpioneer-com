/* jshint esversion: 6 */
/* globals module, require */
const {getConfig} = require('@craftcms/webpack');

module.exports = getConfig({
  context: __dirname,
  config: {
    entry: {
      AnimationBlocker: './AnimationBlocker.ts',
    },
    output: {
      library: {
        name: 'Craft',
        type: 'assign-properties',
      },
    },
  },
});
