# magento connect credentials are provided via encrypted source unless
# the build is public in which case the public credentials are used.
if [ -z "${MAGENTO_CONNECT_USERNAME:-}" ]; then
    export MAGENTO_CONNECT_USERNAME=adba1dac261c621b4b8d154da0d74f15
    export MAGENTO_CONNECT_PASSWORD=096d8c703c14d84af9980fe040e014d9
fi
