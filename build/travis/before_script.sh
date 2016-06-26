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

    N98_MAGERUN2_INSTALL_STATUS=$?
    echo "magerun magento install exit code: ${N98_MAGERUN2_INSTALL_STATUS}"

    if [ ${N98_MAGERUN2_INSTALL_STATUS} -ne 0 ]; then

        # verify magento connect credentials
        (
            build/sh/magento_verify_repo_credentials.sh
        )
        if [ $? -ne 0 ]; then
            echo "problems to connect to repository, allow setup to fail with ${MAGENTO_VERSION}"
            # remove test environment so that test-suite does not think it's installed
            rm -rf "./${MAGENTO_VERSION}"
            unset N98_MAGERUN2_TEST_MAGENTO_ROOT
        else
            exit ${N98_MAGERUN2_INSTALL_STATUS}
        fi

    fi

else
    echo "no magento version to install"
fi
