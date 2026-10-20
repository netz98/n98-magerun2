const assert = require('node:assert/strict')
const fs = require('node:fs')
const path = require('node:path')
const test = require('node:test')
const vm = require('node:vm')

// Exercise the actual config without installing packages or loading any .env file.
function loadConfig (version) {
  const filename = path.resolve(__dirname, '../docusaurus.config.js')
  const source = fs.readFileSync(filename, 'utf8').replace(
    /^import \{ (.+) \} from '([^']+)'$/gm,
    (_, bindings, name) => `const { ${bindings.replace(/ as /g, ': ')} } = require('${name}')`
  )
  const modules = {
    'prism-react-renderer': { themes: { github: {}, darkTheme: {} } },
    dotenv: { configDotenv () {} },
    '@docusaurus/core/package.json': { version }
  }
  const requireStub = (name) => {
    assert.ok(Object.hasOwn(modules, name), `Unexpected config dependency: ${name}`)
    return modules[name]
  }
  requireStub.resolve = name => name
  const context = vm.createContext({
    require: requireStub,
    module: { exports: {} },
    process: { env: {} }
  })
  vm.runInContext(source, context, { filename })
  return context.module.exports
}

for (const [version, key] of [
  ['3.9.2', 'experimental_faster'],
  ['3.9.99', 'experimental_faster'],
  ['3.10.0', 'faster'],
  ['3.10.2', 'faster'],
  ['3.11.0', 'faster']
]) {
  test(`Docusaurus ${version} enables Faster with only its supported key`, () => {
    const config = loadConfig(version)
    assert.equal(config.future[key], true)
    assert.equal(config.future.v4, true)
    assert.deepEqual(Object.keys(config.future).sort(), [key, 'v4'].sort())
    assert.equal(config.onBrokenLinks, 'throw')
  })
}

test('the reported 3.10.2 install does not inherit the lockfile legacy key', () => {
  const config = loadConfig('3.10.2')
  assert.equal(Object.hasOwn(config.future, 'experimental_faster'), false)
  assert.equal(config.future.faster, true)
})
