const assert = require('node:assert/strict')
const test = require('node:test')
const manifest = require('../package.json')
const lock = require('../package-lock.json')

test('direct Docusaurus dependencies lock to the same release as core', () => {
  const coreVersion = lock.packages['node_modules/@docusaurus/core'].version
  const dependencies = { ...manifest.dependencies, ...manifest.devDependencies }
  for (const name of Object.keys(dependencies).filter(name => name.startsWith('@docusaurus/'))) {
    assert.equal(lock.packages[`node_modules/${name}`].version, coreVersion, name)
  }
})

test('Faster and its type companions are pinned to the core release', () => {
  const coreVersion = lock.packages['node_modules/@docusaurus/core'].version
  for (const name of ['@docusaurus/faster', '@docusaurus/module-type-aliases', '@docusaurus/types']) {
    assert.equal(manifest.dependencies[name] || manifest.devDependencies[name], coreVersion, name)
  }
})
