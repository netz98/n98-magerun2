# Installation:
#  This assumes that "n98-magerun2.phar" is in your path!
#  Copy to ~/.config/fish/completions/n98-magerun2.phar.fish
# open new or restart existing shell session

for cmd in (n98-magerun2.phar --raw --no-ansi list | sed "s/[[:space:]].*//g");
    complete -f -c n98-magerun2.phar -a $cmd;
end

