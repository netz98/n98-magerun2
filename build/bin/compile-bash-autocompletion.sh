#!/bin/bash
#
# compile-bash-autocompletion
#
# generate bash autocompletion file for magerun
#
set -euo pipefail
IFS=$'\n\t'

header()
{
    cat <<EOF
#!/bin/bash
# Installation:
#  Copy to /etc/bash_completion.d/n98-magerun.phar
# or
#  Append to ~/.bash_completion
# open new or restart existing shell session

EOF
}

base=magerun2
name=n98-${base}
outfile=res/autocompletion/bash/n98-magerun2.phar.bash

if [[ ! -e "bin/${name}" ]]; then
	>&2 echo "error: could not find 'bin/${name}' script"
	exit 1
fi

echo "creating bash autocomplete file (this takes a little moment) ..."

header > "${outfile}"
vendor/bin/symfony-autocomplete --shell=bash -- "bin/${name}" \
  | sed '1d ; $ s/$/.phar '"${name} ${base}"'/' \
  >> "${outfile}"
# sed: remove first line and expand last line to more command names (aliases)

echo "updated \"${outfile}\"."
