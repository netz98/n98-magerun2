---
title: Module Best Practices
---

## If your Command needs a minimal n98-magerun2 version...

Add this method to your command:

i.e. 2.0.9

```php
<?php

// ...

/**
 * @return bool
 */
public function isEnabled()
{
    return version_compare($this->getApplication()->getVersion(), '2.0.9', '>=');
}    
```
