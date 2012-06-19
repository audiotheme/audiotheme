# Getting Started

Place the framework folder in your theme folder:

`git clone git@github.com:thelukemcdonald/audiotheme-framework.git --recursive`

You can rename the framework folder to something simpler like "audiotheme"

In your functions.php

```php
<?php
require_once( get_template_directory() . '/audiotheme/audiotheme.php' );
```

That's it!

Read about adding options and other features in the [Wiki](https://github.com/thelukemcdonald/audiotheme-framework/wiki)