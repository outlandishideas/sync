Sync directory contents over HTTP using PHP
===

Use these classes to recursively sync the contents of two folders on different servers. The source must have
a web server although the directory being synced does not have to be web accessible. The client initiates the
connection and can be either another web server or a command line script.

Example
---

On the server, e.g. `example.com/remote.php`:

```php
require_once 'vendor/autoload.php'; //or include AbstractSync.php and Server.php

const SECRET = '5ecR3t'; //make this long and complicated
const PATH = '/path/to/source'; //sync all files and folders below this path

$server = new \Outlandish\Sync\Server(SECRET, PATH);
$server->run(); //process the request
```

On the client(s):

```php
require_once 'vendor/autoload.php';

const SECRET = '5ecR3t'; //this must match the secret key on the server
const PATH = '/path/to/destination'; //target for files synced from server

$client = new \Outlandish\Sync\Client(SECRET, PATH);
$client->run('http://example.com/remote.php'); //connect to server and start sync
```
