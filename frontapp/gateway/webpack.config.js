'use strict';

var _ = require('lodash'),
    configs = {
        common: require(__dirname +  '/config/common'),
        development: require(__dirname +  '/config/development'),
        production: require(__dirname +  '/config/production'),
    }


var configWeb = _.merge(
    configs.common(__dirname),
    configs[process.env.NODE_ENV](__dirname),
    {mode: process.env.NODE_ENV}
);

module.exports = [configWeb];
