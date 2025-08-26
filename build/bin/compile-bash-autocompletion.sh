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
#  Copy to /etc/bash_completion.d/n98-magerun2.phar
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
# Generate the bash completion and expand the last line to include aliases.
# Do NOT drop the first line — it contains the function signature required by bash.
vendor/bin/symfony-autocomplete --shell=bash -- "bin/${name}" \
  | sed '$ s/$/.phar '"${name} ${base}"'/' \
  >> "${outfile}"

echo "updated \"${outfile}\"."
