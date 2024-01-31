#!/bin/bash

set -euo pipefail;
set -x

REF_TYPE=$(cut -d'/' -f2 <<< "$GITHUB_REF");
TAG=$(cut -d'/' -f3 <<< "$GITHUB_REF");

echo "Deploy tag: $TAG";

# prepare git commit
git config --global user.email "$GIT_USER_EMAIL";
git config --global user.name "$GIT_USER_NAME";

git clone git@github.com:netz98/n98-magerun2-dist.git;

cd n98-magerun2-dist || exit 1;

ls -l ./n98-magerun2;
cp -v ../n98-magerun2.phar ./n98-magerun2;
ls -l ./n98-magerun2;

git add ./n98-magerun2;
git commit -m "Version: $TAG" ./n98-magerun2;
git tag "$TAG";

if [ "$REF_TYPE" = 'tags' ]; then
  echo "----------------------------------------------------"
  echo "Pushing to dist repo."
  echo "----------------------------------------------------"
  git push;
  git push --tags;
else
  echo "----------------------------------------------------"
  echo " Dry run. Not pushing to dist repo."
  echo "----------------------------------------------------"
  git push --dry-run;
fi
