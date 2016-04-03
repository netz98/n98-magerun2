. build/sh/magento_connect.sh

# Prepare magento connect download
(
    composer global config http-basic.repo.magento.com "${MAGENTO_CONNECT_USERNAME}" "${MAGENTO_CONNECT_PASSWORD}"
)

# only install magento if MAGENTO_VERSION has been set
if [ ! -z ${MAGENTO_VERSION+x} ]; then

    echo "installing magento ${MAGENTO_VERSION}"

    export N98_MAGERUN2_TEST_MAGENTO_ROOT="./${MAGENTO_VERSION}"

    bin/n98-magerun2 install \
        --magentoVersionByName="${MAGENTO_VERSION}" --installationFolder="./${MAGENTO_VERSION}" \
        --dbHost=localhost --dbUser=root --dbPass='' --dbName="magento_travis" \
        --installSampleData=${INSTALL_SAMPLE_DATA} --useDefaultConfigParams=yes \
        --baseUrl="http://travis.magento.local/"

else

    echo "no magento version to install"

fi
