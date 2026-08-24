#!/usr/bin/env bats

setup() {
    load 'test_helper/bats-support/load'
    load 'test_helper/bats-assert/load'

    declare PHP_BIN
    if ! PHP_BIN=$(which php); then
      echo "Error: PHP binary not found"
      exit 1
    fi

    if [ -z "$N98_MAGERUN2_BIN" ]; then
      echo "ENV variable N98_MAGERUN2_BIN is missing"
      exit 1
    fi

    if [ -z "$N98_MAGERUN2_TEST_MAGENTO_ROOT" ]; then
      echo "ENV variable N98_MAGERUN2_TEST_MAGENTO_ROOT is missing"
      exit 1
    fi

    export BIN="${PHP_BIN} -f ${N98_MAGERUN2_BIN} -- --no-interaction --root-dir=${N98_MAGERUN2_TEST_MAGENTO_ROOT}"
}

# Speaks minimal MCP JSON-RPC (newline-delimited, per the stdio transport) to call a
# single tool on a freshly started `mcp:server:start` process, and prints only the
# JSON-RPC response line for that tool call.
mcp_call_tool() {
  local include_filter="$1"
  local tool_name="$2"
  local tool_arguments="$3"

  printf '%s\n%s\n%s\n' \
    '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2025-06-18","capabilities":{},"clientInfo":{"name":"bats","version":"1.0"}}}' \
    '{"jsonrpc":"2.0","method":"notifications/initialized"}' \
    "{\"jsonrpc\":\"2.0\",\"id\":2,\"method\":\"tools/call\",\"params\":{\"name\":\"${tool_name}\",\"arguments\":{\"arguments\":\"${tool_arguments}\"}}}" \
    | $BIN "mcp:server:start" --include="${include_filter}" \
    | grep '"id":2'
}

@test "MCP: module:status core proxy command returns actual command output (regression #2074 / #2127)" {
  run mcp_call_tool "module:status" "module_status" ""
  assert_success
  assert_output --partial '"isError":false'
  assert_output --partial "List of enabled modules"
}

@test "MCP: module:status core proxy command forwards boolean flags correctly" {
  run mcp_call_tool "module:status" "module_status" "--enabled"
  assert_success
  assert_output --partial '"isError":false'
  assert_output --partial "Magento_Catalog"
}

@test "MCP: indexer:status core proxy command returns actual command output" {
  run mcp_call_tool "indexer:status" "indexer_status" ""
  assert_success
  assert_output --partial '"isError":false'
  assert_output --partial "Update On"
}

# CommandToolHandler forwards the "arguments" string to a command's single scalar
# argument completely verbatim (see its buildInput() docblock), so a query wrapped
# in quotes as recommended by `db:query --help` reaches QueryCommand with the quotes
# still attached. Regression coverage for #2126.
@test "MCP: db:query strips wrapping double quotes recommended by its own --help text (regression #2126)" {
  run mcp_call_tool "db:query" "db_query" '\"SELECT 1 AS result\"'
  assert_success
  assert_output --partial '"isError":false'
  assert_output --partial "result"
  refute_output --partial "ERROR 1064"
}

@test "MCP: db:query strips wrapping single quotes recommended by its own --help text (regression #2126)" {
  run mcp_call_tool "db:query" "db_query" "'SELECT 1 AS result'"
  assert_success
  assert_output --partial '"isError":false'
  assert_output --partial "result"
  refute_output --partial "ERROR 1064"
}
