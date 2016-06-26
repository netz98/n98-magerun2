#!/bin/bash
set -euo pipefail
IFS=$'\n\t'

MAGENTO_REPO_URL=https://repo.magento.com/packages.json

# source credentials from working directory if available
if [ -f "magento_connect.sh" ]; then
  source "magento_connect.sh"
fi

function validate()
{
    local pulic="${1}"
    local private="${2}"

    echo "verify magento repo connectivity/credentials (${MAGENTO_REPO_URL})"

    curl -s -f -I -u "${pulic}:${private}" "${MAGENTO_REPO_URL}" 2>&1
}

validate "${MAGENTO_CONNECT_USERNAME}" "${MAGENTO_CONNECT_PASSWORD}"
