const assert = require('node:assert/strict')
const fs = require('node:fs')
const path = require('node:path')
const test = require('node:test')

const workflow = fs.readFileSync(path.resolve(__dirname, '../../.github/workflows/docs_build_test.yml'), 'utf8')

test('docs PR builds use an unprivileged event and explicit head checkout', () => {
  assert.match(workflow, /^ {2}pull_request:$/m)
  assert.doesNotMatch(workflow, /pull_request_target/)
  assert.match(workflow, /^permissions:\n {2}contents: read\n/m)
  assert.doesNotMatch(workflow, /\bwrite\b|secrets\.|\.env/)
  assert.match(workflow, /ref: \$\{\{ github\.event\.pull_request\.head\.sha \|\| github\.sha \}\}/)
  assert.match(workflow, /persist-credentials: false/)
})

test('docs PR builds install the lock and report source and installed versions', () => {
  assert.match(workflow, /git log -1 --format=%H/)
  assert.match(workflow, /run: npm ci/)
  assert.doesNotMatch(workflow, /npm (install|update)/)
  assert.match(workflow, /require\('@docusaurus\/core\/package\.json'\)\.version/)
  assert.match(workflow, /require\('@docusaurus\/faster\/package\.json'\)\.version/)
  assert.match(workflow, /node --test tests\/\*\.test\.cjs/)
  assert.match(workflow, /run: npm run build/)
})
