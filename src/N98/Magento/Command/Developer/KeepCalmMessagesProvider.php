<?php

namespace N98\Magento\Command\Developer;

class KeepCalmMessagesProvider
{
    /**
     * @var string[]
     */
    private array $messages = [
        'Keep calm and flush the cache. Again. 🔄',
        'Mission accomplished! Time to reindex all the things. ⏳',
        'You did it! Go grab a coffee while `setup:upgrade` runs. ☕😎',
        'Another day, another deployment! `bin/magento deploy:mode:set production` 🚀',
        'You rock! Your plugins don\'t even break the core. 🤘',
        'Take a deep breath. Exception printing is disabled, but your talent isn\'t. 🧘‍♂️',
        'High five! The job is done! 🙌',
        'Great job! Now go do something that doesn\'t involve XML. 🏖️',
        'EAV doesn\'t scare you! You\'re a superstar! 🌟',
        'Another bug bites the dust! It wasn\'t the cache this time. Probably. 🤔',
        'Push it real good! To production, not just staging. 🚢',
        'Deploy like a boss! Varnish is purged. ✅',
        'No errors, just a perfectly rendered layout. 😎',
        'You\'re on fire! Like a cron job that actually works. 🔥',
        'You\'ve got this! Stronger than a `preference` for a core class. 💪',
        'Another step closer to perfection! Or at least away from a blank page. 🛠️',
        'Celebrate the small wins! Like a successful DI compile. 🎈',
        'You\'re unstoppable! Like an indexer in `update on save` mode. 🚀',
        'Keep calm and ship it! 🚢',
        'You\'re a wizard, developer! You fixed the checkout! 🧙‍♂️',
        'The code gods (and Alan Storm) are pleased! 🙏',
        'You\'ve leveled up! Next stop: Core Contributor. 🆙',
        'Keep squashing those bugs! Check the exception.log. 🐛🔨',
        'You\'re a code ninja, slicing through `.phtml` files! 🥷',
        'Another ticket closed! Now for the other 50 in the backlog. 🎟️',
        'You\'re making magic happen! ✨',
        'Keep calm and review your PRs! 🔍',
        'You\'re the hero this repo deserves! 🦸‍♂️',
        'You\'re a merge master! No conflicts in `di.xml`. 🔀',
        'Keep calm and refactor that observer. 🧹',
        'You\'re the king of commits! Your messages are clearer than the official docs. 👑',
        'Keep calm and `n98-magerun2 sys:check` on. 😉',
        'You\'re a deployment dynamo! Your `setup:di:compile` was faster than light. ⚡',
        'Keep calm and debug! Is it a plugin? An observer? The cache? Who knows! 🐞',
        'You\'re a versioning virtuoso! 🎼',
        'Keep calm and push to prod! But maybe check the crons first. 🚀',
        'You\'re a code commander! 🫡',
        'Keep calm and optimize! Those layout handles won\'t debug themselves. ⚙️',
        'You\'re a test titan! Your integration tests are a thing of beauty. 🦖',
        'Keep calm and stay caffeinated! ☕',
        'You\'re a code architect! Your service contracts are impeccable. 🏛️',
        'Keep calm and commit! 💾',
        'You\'re a code artist! Your `di.xml` is a masterpiece. 🎨',
        'Another module successfully disabled. It\'s a win! 🎉',
        'Your code is cleaner than a fresh Magento install. ✨',
        'You\'ve tamed the beast that is checkout customization. 🦁',
        'You\'ve successfully avoided a `<preference>`. Here, have a cookie. 🍪',
        'May your cache always be warm and your database queries fast. 🙏',
        'You\'re a hotfix hero! Patched before the official release. 🦸‍♀️',
    ];

    /**
     * Returns a random message.
     */
    public function getRandomMessage(): string
    {
        return $this->messages[array_rand($this->messages)];
    }
}
