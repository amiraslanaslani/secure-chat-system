const { createDefaultPreset } = require("ts-jest");

const tsJestTransformCfg = createDefaultPreset().transform;

/** @type {import("jest").Config} **/
module.exports = {
  testEnvironment: "node",
  transform: {
    ...tsJestTransformCfg,
  },
  moduleNameMapper: {
    "^../services/(.*)\\.js$": "../services/$1.ts",
    "^../types/(.*)\\.js$": "../types/$1.ts"
  },
};